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
        // TODO: MAJORLY REFACTOR THIS
        $this->parent()->updateHarvest(["importer_message" => "Indexing ".count($this->parent()->getTaskData("importedRecords"))." records"]);
        foreach ($this->parent()->getTaskData("importedRecords") as $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $index = $ro->indexable_json();
            $ro->setMetadata('solr_doc', json_encode($index));
            $ro->sync();
        }

        // TODO: unindex records in deletedRecords

    }
}