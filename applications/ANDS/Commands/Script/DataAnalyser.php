<?php

namespace ANDS\Commands\Script;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Mycelium\RelationshipSearchService;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;
use Symfony\Component\Console\Helper\ProgressBar;

class DataAnalyser extends GenericScript implements GenericScriptRunnable
{

    // the id array contains the regiostryObject IDs
    // either loaded from a file or queried from DB
    private $id_array = [];
    private $availableParams = ["sync","import-mycelium","index-relationships","index-portal","check","check-portal-index","check-relationships-index","check-mycelium-import", "ids_file", "all-status"];

    //TODO: save the failed records' id in a {method}-{date}--error-id.txt file so it can be loaded by future processes
    public function run(){

        $file = $this->getInput()->getOption('ids_file');
        if ($file) {
            $this->log("loading ids from a file". $file);
            $contents = file_get_contents($file);
            $this->id_array = explode(',', $contents);
        }

        $all_status = $this->getInput()->getOption("all-status");
        if($all_status){
            $ids = RegistryObject::query();
            $missing_record = [];
            // increase memory limit in case we are syncing the entire content
            ini_set('memory_limit', '2048M');
            $this->log("loading ids from dbs_registry.registry_objects with status = ". $all_status);
            // include registryObjects with status published-only or deleted-only, not both
            $this->id_array = $ids->where('status', $all_status)->pluck("registry_object_id")->toArray();
        }

        $params = $this->getInput()->getOption('params');
        if (!$params) {
            $this->log("You have to specify a param: available: ". implode('|', $this->availableParams), "info");
            return;
        }

        switch ($params) {
            case "sync":
                $this->log("Syncing Records: Mycelium Import -> Index relationships -> Index Portal");
                $this->syncRecords();
                break;
            case "import-mycelium":
                $this->log("Importing Records to Mycelium");
                $this->importRecordsToMycelium();
                break;
            case "index-relationships":
                $this->log("Indexing Relationships core");
                $this->indexRelationships();
                break;
            case "index-portal":
                $this->log("Indexing Portal core");
                $this->indexPortal();
                break;
            // to check for record's existence in mycelium and indexes
            case "check":
                $this->log("Checking records: vertexes in Neo4J (Mycelium), SOLR indexes for Relationships and Portal ");
                $this->checkRecords();
                break;
            case "check-portal-index":
                $this->checkPortalIndex();
                break;
            case "check-relationships-index":
                $this->checkRelationshipsIndex();
                break;
            case "check-mycelium-import":
                $this->checkMyceliumImport();
                break;
            default:
                $this->log("Undefined params. Provided $params");
                break;
        }
    }


    /**
     * @return void
     * this method check if the given ro:id (records)
     * are import into mycelium eg Neo4j
     * have relationships index (that is not a 100% because we still have records that aren't related to other records
     * have an index in portal
     */
    private function checkRecords()
    {
        $this->checkMyceliumImport();
        $this->checkRelationshipsIndex();
        $this->checkPortalIndex();
    }

    /** sync records is just insert mycelium, index relationships and index portal
     * @return void
     */
    private function syncRecords(){
        $this->importRecordsToMycelium();
        $this->indexRelationships();
        $this->indexPortal();
    }

    /** import records into Mycelium
     * @return void
     */
    private function importRecordsToMycelium(){
        $cSuccess = 0;
        $aErrored = [];
        print("\n\nIMPORTING ".sizeof($this->id_array)." RECORD(S) IN MYCELIUM\n");
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));
        $progressBar = new ProgressBar($this->getOutput(), sizeof($this->id_array));
        $progressBar->setFormat('ands-command');
        $progressBar->start();
        foreach ($this->id_array as $index => $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            try {
                $result = $myceliumClient->importRecord($record);
                // it just says done with 200,
                if ($result->getStatusCode() === 200) {
                    $cSuccess++;
                } else {
                    $reason = $result->getBody()->getContents();
                    $aErrored[] = $id;
                    $this->log("Failed importing record  {$id} to mycelium. Reason: $reason");
                }
            }catch(\Exception $e){
                $aErrored[] = $id;
                $this->log("Failed importing record {$id} to mycelium. Reason: ".$e->getMessage());
            }
            $progressBar->setMessage("Indexed: $cSuccess Failed:".sizeof($aErrored)." ro_id:$id");
            $progressBar->advance();
        }
        $progressBar->setMessage("Done");
        $progressBar->finish();
        print("\nNUMBER OF RECORDS SUCCESSFULLY IMPORTED:".$cSuccess." OUT OF:".sizeof($this->id_array). "\n");
        foreach($aErrored as $id) {
            print("$id,");
        }
    }

    /**
     * indexes records' relationships using Mycelium
     * @return void
     */
    private function indexRelationships(){
        $cSuccess = 0;
        $aErrored = [];
        print("\n\nINDEXING RELATIONSHIPS FOR ".sizeof($this->id_array)." RECORD(S)\n");
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));
        $progressBar = new ProgressBar($this->getOutput(), sizeof($this->id_array));
        $progressBar->setFormat('ands-command');
        $progressBar->start();
        foreach ($this->id_array as $index => $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            try{
                $result = $myceliumClient->indexRecord($record);
                // it just says done with 200,
                if ($result->getStatusCode() === 200) {
                    $cSuccess++;
                } else {
                    $reason = $result->getBody()->getContents();
                    $this->log("Failed to index Relationships of Record {$id} by mycelium. Reason: ".$reason);
                    $aErrored[] = $id;
                }
            }
            catch(\Exception $e){
                $aErrored[] = $id;
                $this->log("Failed to index Relationships of Record {$id} by mycelium. Reason: ".$e->getMessage());
            }
            $progressBar->setMessage("Indexed: $cSuccess Failed:".sizeof($aErrored)." ro_id:$id");
            $progressBar->advance();
        }
        $progressBar->setMessage("Done");
        $progressBar->finish();
        print("\nNUMBER OF RECORDS' RELATIONSHIPS INDEXED:".$cSuccess." OUT OF:".sizeof($this->id_array). "\n");
        foreach($aErrored as $id) {
            print("$id,");
        }
    }

    /** index records solr portal core
     * @return void
     */
    private function indexPortal(){

        if (sizeof($this->id_array) == 0) {
            $this->log("No records needed to be indexed");
            return;
        }
        $cSuccess = 0;
        $aErrored = [];
        print("\n\nINDEXING PORTAL FOR ".sizeof($this->id_array)." RECORD(S)\n");
        $progressBar = new ProgressBar($this->getOutput(), sizeof($this->id_array));
        $progressBar->setFormat('ands-command');
        $progressBar->start();
        foreach ($this->id_array as $id) {

            // index without relationship data
            try {
                $record = RegistryObjectsRepository::getRecordByID($id);

                $portalIndex = RIFCSIndexProvider::get($record);
                $this->insertSolrDoc($portalIndex);
                $cSuccess++;
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if (!$msg) {
                    $msg = implode(" ", array_first($e->getTrace())['args']);
                }
                $aErrored[] = $id;
                $this->log($msg);
            }
            $progressBar->setMessage("Indexed: $cSuccess Failed:".sizeof($aErrored)." ro_id:$id");
            $progressBar->advance();
            // save last_sync_portal
            DatesProvider::touchSync($record);
        }
        $progressBar->setMessage("Done");
        $progressBar->finish();
        print("\nNUMBER OF RECORDS INDEXED (PORTAL):".$cSuccess." OUT OF:".sizeof($this->id_array). "\n");
        foreach($aErrored as $id) {
            print("$id,");
        }

    }


    /**
     * @throws \Exception
     */
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


    /**
     * @return void
     * Checks if there is a vertex with ro:id in Neo4j
     */
    private function checkMyceliumImport(){
        print("\n\nCHECKING MYCELIUM ENTRIES OF ".sizeof($this->id_array)." RECORD(S)\n");
        $progressBar = new ProgressBar($this->getOutput(), sizeof($this->id_array));
        $progressBar->setFormat('ands-command');
        $progressBar->start();
        $missing_from_neo = [];
        $cSuccess = 0;
        $client = new MyceliumServiceClient(Config::get('mycelium.url'));
        foreach($this->id_array as $id){
            $record = RegistryObjectsRepository::getRecordByID($id);
            if($record != null) {
                try{
                    $result = $client->getVertex($record);
                    $vertex = json_decode($result->getBody());
                    if ($vertex == null || $id != $vertex->identifier) {
                        $missing_from_neo[] = $id;
                    }
                    else{
                        $cSuccess++;
                    }
                }catch(\Exception $e){
                    $missing_from_neo[] = $id;
                    $msg = $e->getMessage();
                    if (!$msg) {
                        $msg = implode(" ", array_first($e->getTrace())['args']);
                    }
                    $this->log($msg);
                }
            }else{
                $missing_from_neo[] = $id;
            }
            $progressBar->setMessage("Found: $cSuccess Missing:".sizeof($missing_from_neo));
            $progressBar->advance();
        }
        $progressBar->setMessage("Done");
        $progressBar->finish();
        print("\nNUMBER OF RECORDS MISSING FROM MYCELIUM:".sizeof($missing_from_neo)."\n");
        foreach($missing_from_neo as $id) {
            print("$id,");
        }
    }

    /**
     * @return void
     * check if the record is related to any other records
     */
    private function checkRelationshipsIndex(){

        $missing_from_relationships_index = [];
        print("\n\nCHECKING RELATIONSHIPS INDEX FOR ".sizeof($this->id_array)." RECORD(S)\n");
        $progressBar = new ProgressBar($this->getOutput(), sizeof($this->id_array));
        $progressBar->setFormat('ands-command');
        $progressBar->start();
        $cSuccess = 0;
        foreach($this->id_array as $id){
            try{
                $result = RelationshipSearchService::search([
                    'from_id' => $id
                ]);
                if($result->total == 0){
                    $missing_from_relationships_index[] = $id;
                }else{
                    $cSuccess++;
                }
            }catch(\Exception $e){
                $missing_from_relationships_index[] = $id;
                $msg = $e->getMessage();
                if (!$msg) {
                    $msg = implode(" ", array_first($e->getTrace())['args']);
                }
                $this->log($msg);
            }

            $progressBar->setMessage("Found: $cSuccess Missing:".sizeof($missing_from_relationships_index));
            $progressBar->advance();
        }
        $progressBar->setMessage("Done");
        $progressBar->finish();
        print("\nNUMBER OF RECORDS MISSING FROM RELATIONSHIPS INDEX:".sizeof($missing_from_relationships_index)."\n");
        foreach($missing_from_relationships_index as $id) {
            print("$id,");
        }
    }

    /**
     * @return void
     * Checks if the record has a portal index
     */
    private function checkPortalIndex(){
        $missing_portal_index = [];
        $cSuccess = 0;
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $this->log("\n\nCHECKING PORTAL INDEX FOR ".sizeof($this->id_array)." RECORD(S)\n");
        $progressBar = new ProgressBar($this->getOutput(), sizeof($this->id_array));
        $progressBar->setFormat('ands-command');
        $progressBar->start();
        foreach($this->id_array as $id) {
            try{
                $record = $solrClient->get($id);
                if($record == null || $solrClient->hasError()){
                    $missing_portal_index[] = $id;
                }else{
                    $cSuccess++;
                }
            }catch(\Exception $e){
                $missing_portal_index[] = $id;
                $msg = $e->getMessage();
                if (!$msg) {
                    $msg = implode(" ", array_first($e->getTrace())['args']);
                }
                $this->log($msg);
            }
            $progressBar->setMessage("Found: $cSuccess Missing:".sizeof($missing_portal_index));
            $progressBar->advance();
        }
        $progressBar->setMessage("Done");
        $progressBar->finish();
        print("\nNUMBER OF RECORDS MISSING FROM PORTAL INDEX:".sizeof($missing_portal_index)."\n");
        foreach($missing_portal_index as $id) {
            print("$id,");
        }
    }

}