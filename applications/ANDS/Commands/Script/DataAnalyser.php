<?php

namespace ANDS\Commands\Script;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use ANDS\Util\XMLUtil;
use MinhD\SolrClient\SolrClient;

class DataAnalyser extends GenericScript implements GenericScriptRunnable
{
    // should make it either to read a file or pass in idlist as params
    // the id array contains the regiostryObject IDs
    private $id_array = [11158];
    private $availableParams = ["check","mycelium-import","mycelium-index","portal-index","sync", "idx-remote"];

    public function run(){
        $params = $this->getInput()->getOption('params');
        if (!$params) {
            $this->log("You have to specify a param: available: ". implode('|', $this->availableParams), "info");
            return;
        }

        switch ($params) {
            case "check":
                $this->log("checking records");
                $this->checkRecords();
                break;
            case "mycelium-import":
                $this->log("Importing Records to Mycelium");
                $this->importRecordsToMycelium();
                break;
            case "mycelium-index":
                $this->log("Indexing Relationships core");
                $this->indexRelationships();
                break;
            case "portal-index":
                $this->log("Indexing Portal core");
                $this->indexPortal();
                break;
            case "sync":
                $this->log("Import -> Index relationships -> Index Portal");
                $this->syncRecords();
                break;
            case "idx-remote":
                $this->log("Index Remote");
                $this->indexRemoteURL();
                break;
            default:
                $this->log("Undefined params. Provided $params");
                break;
        }
    }

    private function checkRecords()
    {
        $wrong_keys = [];
        $missing_from_neo = [];
        $exists_in_neo = [];
        $c = 0;
        print("__________________");
        $client = new MyceliumServiceClient(Config::get('mycelium.url'));
        foreach($this->id_array as $id){
            $record = RegistryObjectsRepository::getRecordByID($id);
            $key = $record->key;
            $xml = $record->getCurrentData()->data;

            $registryObject = XMLUtil::getElementsByName($xml, 'registryObject');
            $rif_key = trim((string) $registryObject[0]->key);
            if($rif_key != $key){
                $wrong_keys[] = $id;
            }else{
                $result = $client->getVertex($record);
                $vertex = json_decode($result->getBody());
                if($vertex != null && $id == $vertex->identifier){
                    $exists_in_neo[] = $id;
                }else{
                    $missing_from_neo[] = $id;
                }
            }
        }
        print("\n________WRONG KEYS__________".sizeof($wrong_keys)."___\n");

        foreach($wrong_keys as $id) {
            print("$id,");
        }
        print("\n________MISSING FROM NEO__________".sizeof($missing_from_neo)."___\n");
        foreach($missing_from_neo as $id) {
            print("$id,");
        }
        print("\n________PROD UPDATE ERROR__________".sizeof($exists_in_neo)."___\n");
        foreach($exists_in_neo as $id) {
            print("$id,");
        }


    }

    /**
     * quick hack to index prod records until prod has command scripts to do so
     * @return void
     */
    private function indexRemoteURL()
    {
        foreach ($this->id_array as $index => $id) {
            try {
                $url = "https://researchdata.edu.au/api/registry/object/" . $id . "/sync";
                $content = \ANDS\Util\URLUtil::file_get_contents($url);
                print($content);
            } catch (\Exception $e) {
                print("Failed importing record {$id} to mycelium. Reason: " . $e->getMessage());
            }
        }
    }

    /** import records into Mycelium
     * @return void
     */
    private function importRecordsToMycelium(){
        $import_count = 0;
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));
        foreach ($this->id_array as $index => $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            try {
                $result = $myceliumClient->importRecord($record);
                // it just says done with 200,
                if ($result->getStatusCode() === 200) {
                    $this->log("imported ro id: $id ($index)");
                    $import_count++;
                } else {
                    $reason = $result->getBody()->getContents();
                    print("Failed to index record {$id} to mycelium. Reason: $reason");
                }
            }catch(\Exception $e){
                print("Failed importing record {$id} to mycelium. Reason: ".$e->getMessage());
            }
        }
    }

    /**
     * indexes records' relationships using Mycelium
     * @return void
     */
    private function indexRelationships(){
        $indexed_count = 0;
        $error_count = 0;
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));

        foreach ($this->id_array as $index => $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            try{
            $result = $myceliumClient->indexRecord($record);
            // it just says done with 200,
            if ($result->getStatusCode() === 200) {
                $this->log("indexed relationship ro id: $id ($index)");
                $indexed_count++;
            } else {
                $reason = $result->getBody()->getContents();
                print("Failed to index record {$id} to mycelium. Reason: $reason");
            }
            }
            catch(\Exception $e){
                print("Failed to index record {$id} to mycelium. Reason: ".$e->getMessage());
            }
        }
        if($indexed_count > 0){
            $this->log("Indexed {$indexed_count} record(s) by mycelium");
        }
        if($error_count > 0){
            $this->log("Failed to Index {$error_count} record(s) by mycelium");
        }
    }

    /** index records solr portal core
     * @return void
     */
    private function indexPortal(){
        $total = count($this->id_array);

        if ($total == 0) {
            $this->log("No records needed to be reindexed");
            return;
        }
        foreach ($this->id_array as $index=>$id) {

            // index without relationship data
            try {
                $record = RegistryObjectsRepository::getRecordByID($id);
                $this->log("indexed portal ro id: $id ($index)");
                $portalIndex = RIFCSIndexProvider::get($record);
                $this->insertSolrDoc($portalIndex);

            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if (!$msg) {
                    $msg = implode(" ", array_first($e->getTrace())['args']);
                }
                print($msg);
            }
            // save last_sync_portal
            DatesProvider::touchSync($record);
        }

        $this->log("Finished Indexing $total records");
    }


    private function insertSolrDoc($json){
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $solrClient->request("POST", "portal/update/json/docs", ['commit' => 'true'],
            json_encode($json), "body");
    }

    /** sync records is just insert mycelium, index relationships and index portal
     * @return void
     */
    private function syncRecords(){
        $this->importRecordsToMycelium();
        $this->indexRelationships();
        $this->indexPortal();
    }

}