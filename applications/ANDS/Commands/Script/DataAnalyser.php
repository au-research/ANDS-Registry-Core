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
    private $id_array = [954547];
    // spatial index issue
    //private $id_array = [476745,691252,954985,961096,954961];
    //
    // long text issue
    //private $id_array = [954547,1541862,1435702,1600668,1588284,616616,1923984,1542078,1542480,1601067,1601073,1547166];
    //private $id_array = [1939353,1937190,1919970,1938978,1941582,1933959,955132,1935258,1918608,1939605,1933329,690964,1792113,1931625,1936446,955153,954973,1932672,1935861,955063,955150,1879464,1938366,993004,476242,1792119,1793721,1793727,1879929,476745,1927872,1793703,691252,954985,1927668,961255,1930566,1930614,1942631,1879935,954925,691237,1933239,476758,955093,1935891,992140,954892,961024,1928943,961096,1933101,1939743,1935102,1939314,1792107,1793718,1941192,1879308,1936950,1937400,961204,1792110,961294,1792080,1879296,1938330,955042,1793733,954961,1305580,1934946,1927500,1937628,1937223,1935948,961309,961117,1917501,954883,1930620,1793730,1304104,1933449,961135,1879098,1931913,1917498,1938384,1940490,1938126,961009,475867,1938672,1933305,1941555,476817,954508,1792098];
    private $availableParams = ["check","mycelium-import","mycelium-index","portal-index","sync", "idx-remote", "id_file"];

    public function run(){

        $file = $this->getInput()->getOption('file');
        if ($file) {
            $this->log("loading ids from a file". $file);
            $contents = file_get_contents($file);
            $this->id_array = explode(',', $contents);
        }

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

                $portalIndex = RIFCSIndexProvider::get($record);
                $this->insertSolrDoc($portalIndex);
                $this->log("indexed portal ro id: $id ($index)");
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if (!$msg) {
                    $msg = implode(" ", array_first($e->getTrace())['args']);
                }
                $this->log("Failed indexing portal ro id: $id ($index)");
                $this->log($msg);
            }
            // save last_sync_portal
            DatesProvider::touchSync($record);
        }

        $this->log("Finished Indexing $total records");
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

    /** sync records is just insert mycelium, index relationships and index portal
     * @return void
     */
    private function syncRecords(){
        $this->importRecordsToMycelium();
        $this->indexRelationships();
        $this->indexPortal();
    }

}