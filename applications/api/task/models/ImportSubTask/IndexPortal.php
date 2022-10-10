<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\Repository\RegistryObjectsRepository as Repo;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;

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

        foreach ($importedRecords as $index=>$roID) {
            try {
                $record = RegistryObjectsRepository::getRecordByID($roID);
                $portalIndex = RIFCSIndexProvider::get($record);
                $this->insertSolrDoc($portalIndex);
                $cSuccess++;
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if(str_contains($msg, 'org.locationtech.jts.geom.TopologyException')){
                    // try indexing without the spatial data if it's invalid WKT
                    try {
                        $portalIndex = RIFCSIndexProvider::get($record, false);
                        $this->insertSolrDoc($portalIndex);
                        $cSuccess++;
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                        if (!$msg) {
                            $msg = implode(" ", array_first($e->getTrace())['args']);
                        }
                        $this->addError("Error getting portalIndex for $roID : $msg");
                    }
                }
                else{
                    $msg = $e->getMessage();
                    if (!$msg) {
                        $msg = implode(" ", array_first($e->getTrace())['args']);
                    }
                    $this->addError("Error getting portalIndex for $roID : $msg");
                }
            }
            // save last_sync_portal
            DatesProvider::touchSync($record);
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title");
        }
        $this->log("Finished Indexing $total records");
    }



    private function insertSolrDoc($json){
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $solrClient->request("POST", "portal/update/json/docs", ['commit' => 'true'],
            json_encode($json), "body");
        if($solrClient->hasError()){
            $msg = $solrClient->getErrors();
            throw new \Exception("ERROR while indexing records".$msg[0]);
        }
    }

}