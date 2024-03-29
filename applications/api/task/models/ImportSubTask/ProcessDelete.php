<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Log\Log;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Registry\Providers\DCI\DCI;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\Scholix\Scholix;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\RegistryObject\Links;
use ANDS\Util\Config;
use ANDS\Util\SolrIndex;

/**
 * Class ProcessDelete
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessDelete extends ImportSubTask
{
    protected $requireDeletedRecords = true;
    public $title = "DELETING RECORDS";
    protected $chunkSize = 500;

    public function run_task()
    {
        $deletedRecords = $this->parent()->getTaskData('deletedRecords');
        $chunks = collect($deletedRecords)->chunk($this->chunkSize);

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
        $ids = collect($records)->pluck('registry_object_id')->toArray();
        // TODO: refactor to reduce SQL queries
        foreach ($records as $record) {
            // if this is a HandleStatusChange pipeline

            if($this->parent()->getTaskByName("HandleStatusChange") != null){
                $publishedRecord = RegistryObjectsRepository::getPublishedByKey($record->key);
                // make sure we have a published version of the DRAFT before deleting it.
                // test for PUBLISHED exists and its harvest_id is the same as the DRAFT's harvest_id
                if($publishedRecord &&
                    $publishedRecord->getRegistryObjectAttributeValue('harvest_id') === $record->getRegistryObjectAttributeValue('harvest_id')){
                    RegistryObjectsRepository::completelyEraseRecordByID($record->registry_object_id);
                }else{
                    $this->log("Record $record->registry_object_id ($record->status) is not DELETED due to missing PUBLISHED version");
                    // remove it from the ids array as well
                    unset($ids[$record->registry_object_id]);
                }
            }
        else{
            RegistryObjectsRepository::completelyEraseRecordByID($record->registry_object_id);
            $this->log("Record $record->registry_object_id ($record->status) is completely DELETED");
        }


        }
        $this->removeRegistryObjectFromGraphDatabase($ids);
        $this->log("DRAFT Records were DELETED from GRAPH DB");
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

        $ids = collect($records)->pluck('registry_object_id')->toArray();

        // delete from the database
        $this->removeRegistryObjectFromDatabaseTables($ids);
        $this->removeRegistryObjectFromGraphDatabase($ids);
        $this->removeRegistryObjectFromPortalSOLR($ids);
    }

    public function removeRegistryObjectFromDatabaseTables($ids)
    {
        $count = count($ids);
        $this->log("Removing Links, Scholix and DCI of $count records");

        Links::whereIn('registry_object_id', $ids)->delete();
        Scholix::whereIn('registry_object_id', $ids)->delete();
        DCI::whereIn('registry_object_id', $ids)->delete();

        // TODO alt schema versions

        $this->log("Soft Deleting $count records");

        // soft delete the registryObject (for PUBLISHED)
        RegistryObject::whereIn('registry_object_id', $ids)->update(['status' => 'DELETED']);

        // touch delete timestamps
        DatesProvider::touchDeleteByIDs($ids);
    }

    public function removeRegistryObjectFromGraphDatabase($ids)
    {
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));

        // affectedRelationships setup
        $result = $myceliumClient->createNewDeleteRecordRequest();
        $request = json_decode($result->getBody()->getContents(), true);
        $myceliumRequestId = $request['id'];
        $this->log("Mycelium Request created ID: $myceliumRequestId");
        $this->parent()->setTaskData("myceliumRequestId", $myceliumRequestId);

        // perform the deletion per record
        foreach ($ids as $id) {
            $result = $myceliumClient->deleteRecord($id, $myceliumRequestId);
            if ($result->getStatusCode() === 200) {
                $this->log("Deleted RegistryObject[id={$id}] from mycelium");
            } else {
                $reason = $result->getBody()->getContents();
                $this->addError("Failed to delete RegistryObject[id={$id}] from mycelium. Reason: $reason");
            }
        }
    }

    public function removeRegistryObjectFromPortalSOLR($ids)
    {

        // collect and build a portalQuery to delete
        // TODO refactor with collections array_map
        $portalQuery = "";
        foreach ($ids as $id) {
            $portalQuery .= " id:$id";
            $this->parent()->incrementTaskData("recordsDeletedCount");
        }

        // chunk the ids by 300
        $chunks = collect($ids)->chunk(300)->toArray();
        foreach ($chunks as $chunk) {

            $portalQuery = collect($chunk)->map(function ($id) {
                return ' id:' . $id;
            })->implode(' ');

            // delete from the solr index
            $result = SolrIndex::getClient('portal')->removeByQuery($portalQuery);
            if ($result['responseHeader']['status'] != 0) {
                $errorMessage = $result['error']['msg'];
                $this->addError("delete index failed " . $errorMessage);
            }
        }
        SolrIndex::getClient('portal')->commit();
    }
}