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
        foreach ($deletedRecords as $id) {
            $record = RegistryObject::find($id);
            if ($record && $record->isPublishedStatus()) {
                // TODO: Refactor Repo::deleteRecord
                $record->status = "DELETED";
                $record->save();
                $this->log("Record $id ($record->status) is set to DELETED");
                $this->parent()->incrementTaskData("recordsDeletedCount");
            } elseif ($record && $record->isDraftStatus()) {
                RegistryObjectsRepository::completelyEraseRecordByID($id);
                $this->log("Record $id ($record->status) is completely DELETED");
                // $this->parent()->incrementTaskData("recordsDeletedCount");
            } else {
                $this->log("Record with ID " . $id . " doesn't exist for deletion");
            }
        }

        $this->parent()->getCI()->load->library('solr');

        $portalQuery = "";
        $fromRelationQuery = "";
        $toRelationQuery = "";
        foreach ($deletedRecords as $id) {
            $portalQuery .= " id:$id";
            $fromRelationQuery .= " from_id:$id";
            $toRelationQuery .= " to_id:$id";
        }

        // Find all affected records and put the affected records to importedRecords list
        // to be ran OptimizeRelationships on

        $affectedRecordIDs = [];
        $tos = $this->parent()->getCI()->solr->init()
            ->setCore('relations')
            ->setOpt('fl', 'to_id, from_id')
            ->setOpt('q', $fromRelationQuery. $toRelationQuery)
            ->executeSearch(true);

        if ($tos['response']['numFound'] > 0) {
            foreach ($tos['response']['docs'] as $doc) {
                $affectedRecordIDs[] = $doc['from_id'];
                $affectedRecordIDs[] = $doc['to_id'];
            }
        }

        $affectedRecordIDs = array_unique($affectedRecordIDs);
        $affectedRecordIDs = array_filter($affectedRecordIDs,
            function($input) use ($deletedRecords) {
                return !in_array($input, $deletedRecords);
            }
        );

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
                $fixRelationshipTask->fixRelationshipRecord($roID);
                $this->log("Fixed relationship on affected record: ". $roID);
            }
        }

        // delete from the solr index
        $this->parent()->getCI()->solr->init()
            ->setCore('portal')->deleteByQueryCondition($portalQuery);
        $this->parent()->getCI()->solr->commit();

        $this->parent()->getCI()->solr->init()
            ->setCore('relations')->deleteByQueryCondition($fromRelationQuery);
        $this->parent()->getCI()->solr->deleteByQueryCondition($toRelationQuery);
        $this->parent()->getCI()->solr->commit();


    }

}