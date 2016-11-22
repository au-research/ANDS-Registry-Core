<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Repository\RegistryObjectsRepository as Repo;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;

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
        $this->parent()->getCI()->load->library('solr');

        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        $this->parent()->updateHarvest(
            ["importer_message" => "Indexing $total importedRecords"]
        );

        // TODO: MAJORLY REFACTOR THIS
        foreach ($importedRecords as $index=>$roID) {

            $ro = $this->parent()->getCI()->ro->getByID($roID);

            // index without relationship data
            $portalIndex = $ro->indexable_json(null, []);
            if (count($portalIndex) > 0) {
                // TODO: Check response
                $this->parent()->getCI()->solr->init()->setCore('portal');
                $this->parent()->getCI()->solr
                    ->deleteByID($roID);
                $this->parent()->getCI()->solr
                    ->addJSONDoc(json_encode($portalIndex));
            }

            $this->updateProgress($index, $total, "Processed ($index/$total) $ro->title($roID)");
        }

         $this->parent()->getCI()->solr->init()->setCore('portal')->commit();
    }
}