<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\DCI\DCI;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\Scholix\Scholix;
use ANDS\RegistryObject;
use ANDS\RegistryObject\IdentifierRelationship;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject\ImplicitRelationship;
use ANDS\RegistryObject\Relationship;
use ANDS\RegistryObject\Links;

/**
 * Class ProcessDelete
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessDelete extends ImportSubTask
{
    protected $requireDeletedRecords = true;
    protected $title = "DELETING RECORDS";
    protected $chunkSize = 500;

    public function run_task()
    {
        $deletedRecords = $this->parent()->getTaskData('deletedRecords');
        $chunks = collect($deletedRecords)->chunk(self::$chunkSize);

        // chunk the deleted records for performance
        foreach ($chunks as $i => $chunk) {
            $statuses = RegistryObject::select('status')
                ->groupBy('status')
                ->whereIn('registry_object_id', $chunk)
                ->get()->pluck('status')->toArray();

            // if there's only 1 status, then take the first status
            if (count($statuses) === 1) {
                $status = $statuses[0];
                if (RegistryObjectsRepository::isPublishedStatus($status)) {
                    $this->deletePublishedRecords($chunk);
                } elseif (RegistryObjectsRepository::isDraftStatus($status)) {
                    $this->deleteDraftRecords($chunk);
                } else {
                    $this->log("Chunk #$i status is not valid for deletion: $status");
                }
            } else {
                // more than 1 status, determine the status of each record in this chunk
                $publishedRecords = [];
                $draftRecords = [];
                foreach ($chunk as $id) {
                    $record = RegistryObject::find($id);
                    if ($record && $record->isPublishedStatus()) {
                        $publishedRecords[] = $record->id;
                    } elseif ($record && $record->isDraftStatus()) {
                        $draftRecords[] = $record->id;
                    } else {
                        $this->log("Record with ID " . $id . " does not exist for deletion");
                    }
                }

                if (count($draftRecords) > 0) {
                    $this->deleteDraftRecords($draftRecords);
                }

                if (count($publishedRecords) > 0) {
                    $this->deletePublishedRecords($publishedRecords);
                }
            }
        }
    }

    /**
     * deleting DRAFT RegistryObject
     * will delete every instance of this records
     *
     * @param $chunk
     */
    public function deleteDraftRecords($chunk)
    {
        $count = count($chunk);
        $this->log("Deleting $count DRAFT records");

        $records = RegistryObject::whereIn('registry_object_id', $chunk)->get();

        // TODO: refactor to reduce SQL queries
        foreach ($records as $record) {
            RegistryObjectsRepository::completelyEraseRecordByID($record->registry_object_id);
            $this->log("Record $record->registry_object_id ($record->status) is completely DELETED");
        }
    }

    /**
     * A mini workflow to delete published RegistryObject
     * soft-delete implementation
     *
     * @param $chunk
     */
    public function deletePublishedRecords($chunk)
    {
        $records = RegistryObject::whereIn('registry_object_id', $chunk)->get();

        $count = count($records);
        $this->log("Deleting $count PUBLISHED records");

        // placeholder for index queries
        $portalQuery = "";
        $fromRelationQuery = "";
        $toRelationQuery = "";

        $ids = collect($records)->pluck('registry_object_id')->toArray();
        $keys = collect($records)->pluck('key')->toArray();

        // get the affected records IDs before deleting the PUBLISHED records
        $affectedRecordIDs = RelationshipProvider::getAffectedIDsFromIDs($ids, $keys);

        // delete all relevant information
        RegistryObject\Identifier::whereIn('registry_object_id', $ids)->delete();
        IdentifierRelationship::whereIn('registry_object_id', $ids)->delete();
        Relationship::whereIn('registry_object_id', $ids)->delete();
        ImplicitRelationship::whereIn('from_id', $ids)->delete();
        Links::whereIn('registry_object_id', $ids)->delete();
        Scholix::whereIn('registry_object_id', $ids)->delete();
        DCI::whereIn('registry_object_id', $ids)->delete();

        // TODO alt schema versions

        // set status to soft deleted
        RegistryObject::whereIn('registry_object_id', $ids)->update(['status' => 'DELETED']);

        // touch delete timestamps
        DatesProvider::touchDeleteByIDs($ids);

        // TODO refactor with collections array_map
        foreach ($records as $record) {
            $portalQuery .= " id:$record->registry_object_id";
            $fromRelationQuery .= " from_id:$record->registry_object_id";
            $toRelationQuery .= " to_id:$record->registry_object_id";
            $this->parent()->incrementTaskData("recordsDeletedCount");
        }

        // remove from the graph database
        try {
            GraphRelationshipProvider::bulkDelete($records);
        } catch (\Exception $e) {
            $this->addError("Error deleting graph relationships: ". get_exception_msg($e));
        }

        // there are nothing to be added to the affected here, because there
        // should be no new identifiers or anything created after the delete

        $this->log("Size of affected records: ".count($affectedRecordIDs));

        // get currently affected records, if set, merge
        $currentAffectedRecords = $this->parent()->getTaskData('affectedRecords');
        if ($currentAffectedRecords) {
            $affectedRecordIDs = array_merge($currentAffectedRecords, $affectedRecordIDs);
        }

        // only set if affected is greater than 0
        if (sizeof($affectedRecordIDs) > 0) {
            $this->parent()->setTaskData('affectedRecords', $affectedRecordIDs);
        }

        // TODO optimization
        $this->parent()->getCI()->load->library('solr');
        // chunk the ids by 300
        $chunks = collect($ids)->chunk(300)->toArray();
        foreach ($chunks as $chunk) {

            $portalQuery = collect($chunk)->map(function($id) {
                return ' id:'.$id;
            })->implode(' ');

            $relationsQuery = collect($chunk)->map(function($id) {
                return ' from_id:'.$id. ' to_id:'. $id;
            })->implode(' ');

            // delete from the solr index
            $result = $this->parent()->getCI()->solr->init()
                ->setCore('portal')
                ->deleteByQueryCondition($portalQuery);

            $result = json_decode($result, true);
            if (array_key_exists('error', $result)) {
                $this->addError("unindexing failed ". $result['error']['msg']);
            }

            $result = $this->parent()->getCI()->solr->init()
                ->setCore('relations')
                ->deleteByQueryCondition($relationsQuery);

            $result = json_decode($result, true);
            if (array_key_exists('error', $result)) {
                $this->addError("unindexing failed ". $result['error']['msg']);
            }
        }

        $this->parent()->getCI()->solr->init()->setCore('portal')->commit();
        $this->parent()->getCI()->solr->init()->setCore('relations')->commit();
    }
}