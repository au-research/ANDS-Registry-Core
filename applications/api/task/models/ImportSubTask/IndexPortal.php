<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Repository\RegistryObjectsRepository as Repo;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use Illuminate\Support\Collection;

/**
 * Class IndexPortal
 * @package ANDS\API\Task\ImportSubTask
 */
class IndexPortal extends ImportSubTask
{
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

        $importedRecords = $this->parent()->getTaskData("importedRecords") ? $this->parent()->getTaskData("importedRecords") : [];

        $total = count($importedRecords);

        if ($total == 0) {
            $this->log("No records needed to be reindexed");
            return;
        }

        $this->log("Indexing $total records");

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
                $this->parent()->getCI()->solr->deleteByID($roID);
                $this->parent()->getCI()->solr->commit();
                $result = $this->parent()->getCI()
                    ->solr->add_json(json_encode(
                        ['add' => ["doc" => $portalIndex]]
                    ));
                $result = json_decode($result, true);
                if (array_key_exists('error', $result)) {
                    $this->addError("portal for $ro->id: ". $result['error']['msg']);
                }
            }

            $this->updateProgress($index, $total, "Processed ($index/$total) $ro->title($roID)");
        }

        $result = $this->parent()->getCI()->solr->init()->setCore('portal')->commit();
        $result = json_decode($result, true);
        if (array_key_exists('error', $result)) {
            $this->addError("commit: ". $result['error']['msg']);
        }
    }
}