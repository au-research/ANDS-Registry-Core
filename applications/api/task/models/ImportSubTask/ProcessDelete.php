<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\API\Task\FixRelationshipTask;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class ProcessDelete
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessDelete extends ImportSubTask
{
    protected $requireDeletedRecords = true;
    protected $title = "DELETING RECORDS";

    public function run_task()
    {
        $deletedRecords = $this->parent()->getTaskData('deletedRecords');

        $publishedRecords = [];
        $draftRecords = [];
        foreach ($deletedRecords as $id) {
            $record = RegistryObject::find($id);
            if ($record && $record->isPublishedStatus()) {
                $publishedRecords[] = $record;
            } elseif ($record && $record->isDraftStatus()) {
                $draftRecords[] = $record;
            } else {
                $this->log("Record with ID " . $id . " doesn't exist for deletion");
            }
        }

        if (count($draftRecords) > 0) {
            $this->deleteDraftRecords($draftRecords);
        }

        if (count($publishedRecords) > 0) {
            $this->deletePublishedRecords($publishedRecords);
        }
    }

    public function deleteDraftRecords($records)
    {
        $count = count($records);
        $this->log("Deleting $count DRAFT records");

        // TODO: refactor to reduce SQL queries
        foreach ($records as $record) {
            RegistryObjectsRepository::completelyEraseRecordByID($record->registry_object_id);
            $this->log("Record $record->registry_object_id ($record->status) is completely DELETED");
        }
    }

    public function deletePublishedRecords($records)
    {
        $deletedRecords = $this->parent()->getTaskData('deletedRecords');

        $count = count($records);
        $this->log("Deleting $count PUBLISHED records");

        // placeholder for index queries
        $portalQuery = "";
        $fromRelationQuery = "";
        $toRelationQuery = "";

        // TODO: refactor to reduce SQL queries
        foreach ($records as $record) {
            $record->status = "DELETED";
            $record->save();
            $this->log("Record $record->registry_object_id ($record->status) is set to DELETED");

            $portalQuery .= " id:$record->registry_object_id";
            $fromRelationQuery .= " from_id:$record->registry_object_id";
            $toRelationQuery .= " to_id:$record->registry_object_id";

            $this->parent()->incrementTaskData("recordsDeletedCount");
        }

        $this->parent()->getCI()->load->library('solr');

        // Find all affected records and put the affected records to importedRecords list
        // to be ran OptimizeRelationships on
        $affectedRecordIDs = $this->getRelatedObjectsIDs($deletedRecords);

        $affectedRecordIDs = array_values($affectedRecordIDs);
        $this->log("Size of affected records: ".count($affectedRecordIDs));

        // TODO: Find a way to use OptimizeRelationship subtask instead
        // Probably put the affectedRecordIDs to the importedRecords array instead
        if (count($affectedRecordIDs) > 0) {
            $this->parent()->getCI()
                ->load->model('registry/registry_object/registry_objects', 'ro');
            $fixRelationshipTask = new FixRelationshipTask();
            $fixRelationshipTask->setCI($this->parent()->getCI())->init([]);
            foreach ($affectedRecordIDs as $index => $roID) {
                try {
                    $fixRelationshipTask->fixRelationshipRecord($roID);
                } catch (\Exception $e) {
                    $this->addError("Error whilst fixing relationship for record: $roID : $e->getMessage()");
                }
                $this->log("Fixed relationship on affected record: ". $roID);
            }
        }

        // delete from the solr index
        $this->parent()->getCI()->solr->init()
            ->setCore('portal')
            ->deleteByQueryCondition($portalQuery);
        // $this->parent()->getCI()->solr->commit();

        $this->parent()->getCI()->solr->init()
            ->setCore('relations')
            ->deleteByQueryCondition($fromRelationQuery.$toRelationQuery);
        // $this->parent()->getCI()->solr->commit();
    }


    /**
     * Returns a list of related ids given a list of ids
     * Useful to get affected ids
     * Using SOLR relations core
     *
     * @param $ids
     * @return array
     */
    public function getRelatedObjectsIDs($ids)
    {
        $chunkSize = 300;

        // chunk the ids
        $ids = collect($ids);
        $chunks = $ids->chunk($chunkSize);

        // foreach chunk, run the query, append the result
        $allAffectedIDs = [];
        foreach ($chunks as $chunk) {
            $fromRelationQuery = ' from_id:'.$chunk->implode(' from_id:');
            $toRelationQuery = ' to_id:'.$chunk->implode(' to_id:');

            $affectedRecordIDs = [];
            $tos = $this->parent()->getCI()->solr->init()
                ->setCore('relations')
                ->setOpt('fl', 'to_id, from_id')
                ->setOpt('rows', 10000)
                ->setOpt('q', $fromRelationQuery . $toRelationQuery)
                ->executeSearch(true);

            if ($tos['response']['numFound'] > 0) {
                foreach ($tos['response']['docs'] as $doc) {
                    $affectedRecordIDs[] = $doc['from_id'];
                    $affectedRecordIDs[] = $doc['to_id'];
                }
            }
            $affectedRecordIDs = array_unique($affectedRecordIDs);
            $allAffectedIDs = array_merge($allAffectedIDs, $affectedRecordIDs);
        }

        $allAffectedIDs = array_unique($allAffectedIDs);

        return $allAffectedIDs;
    }


}