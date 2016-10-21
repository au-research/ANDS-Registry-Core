<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Repository\RegistryObjectsRepository as Repo;
use ANDS\Repository\DataSourceRepository;

class IndexPortal extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "INDEXING PORTAL";

    public function run_task()
    {
        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!Repo::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' No indexing required');
            return;
        }

        $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
        $this->parent()->updateHarvest(["importer_message" => "Indexing ".count($this->parent()->getTaskData("importedRecords"))." records"]);
        $importedRecords = $this->parent()->getTaskData("importedRecords");

        // TODO: MAJORLY REFACTOR THIS
        foreach ($importedRecords as $index=>$roID) {
            $this->updateProgress($index, count($importedRecords), "Processing ". $roID);
            $ro = $this->parent()->getCI()->ro->getByID($roID);

            // $ro->setMetadata('solr_doc', json_encode($index));

            $index = $ro->indexable_json();
            $this->parent()->getCI()->solr->init()->setCore('portal')->addDoc(json_encode([$index]));

            $relationIndex = $ro->getRelationshipIndex();
            $this->parent()->getCI()->solr->init()->setCore('relations')->addDoc(json_encode([$relationIndex]));
        }

        $this->parent()->getCI()->solr->init()->setCore('portal')->commit();
        $this->parent()->getCI()->solr->init()->setCore('relations')->commit();

        // TODO: unindex records in deletedRecords

    }
}