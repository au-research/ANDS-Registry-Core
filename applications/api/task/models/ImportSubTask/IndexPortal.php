<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Repository\RegistryObjectsRepository as Repo;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use Carbon\Carbon;
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
            $record = RegistryObjectsRepository::getRecordByID($roID);
            $portalIndex = [];
            // index without relationship data
            try {
                $portalIndex = $ro->indexable_json(null, []);
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if (!$msg) {
                    $msg = implode(" ", array_first($e->getTrace())['args']);
                }
                $this->addError("Error getting portalIndex for $ro->id : $msg");
            }

            if (count($portalIndex) > 0) {
                // TODO: Check response
                $this->parent()->getCI()->solr->init()->setCore('portal');
                $this->parent()->getCI()->solr->deleteByID($roID);
//                $this->parent()->getCI()->solr->commit();
                $result = $this->parent()->getCI()
                    ->solr->add_json(json_encode(
                        ['add' => ["doc" => $portalIndex]]
                    ));
                $result = json_decode($result, true);

                if ($result === null) {
                    $this->addError("portal for $ro->id failed : unknown reason");
                    continue;
                }

                if (array_key_exists('error', $result)) {
                    $this->addError("portal for $ro->id: ". $result['error']['msg']);
                    continue;
                }

                // save last_sync_portal
                $record->setRegistryObjectAttribute('indexed_portal_at', Carbon::now()->timestamp);
            }

            $this->updateProgress($index, $total, "Processed ($index/$total) $ro->title($roID)");
        }

//        $result = $this->parent()->getCI()->solr->init()->setCore('portal')->commit();
//        $result = json_decode($result, true);
//        if (array_key_exists('error', $result)) {
//            $this->addError("commit: ". $result['error']['msg']);
//        }

        $this->log("Finished Indexing $total records");
    }
}