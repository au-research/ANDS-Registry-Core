<?php
/**
 * Class:  GraphTask
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;

use \Exception as Exception;
use Neoxygen\NeoClient\ClientBuilder;

/**
 * Class SyncTask
 *
 * @package ANDS\API\Task
 */
class GraphTask extends Task
{
    private $client;
    private $chunkSize = 100;

    public function run_task()
    {
        $this->ci =& get_instance();
        $neo4jConf = get_config_item('neo4j');

        try {
            $this->loadParams();
            $this->constructClient($neo4jConf);

            $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
            $this->ci->load->model('registry/data_source/data_sources', 'ds');

            switch ($this->target) {
                case 'ds':
                    $list = explode(',', $this->target_id);
                    foreach ($list as $dsID) {
                        if ($dsID) {
                            $this->syncDS($dsID);
                        } else {
                            throw new Exception("No valid Data Source ID found");
                        }
                    }
                    break;
                case 'ro':
                    $list = explode(',', $this->target_id);
                    $this->syncRo($list);
                    break;
            }

        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    private function syncDS($dsID){
        if (!$this->chunkSize) {
            throw new Exception("No chunk defined for this sync task");
        }
        if (!$dsID) {
            throw new Exception("Data Source ID required");
        }
        $offset = ($this->chunkPos - 1) * $this->chunkSize;
        $limit = $this->chunkSize;
        $ids = $this->ci->ro->getIDsByDataSourceID($dsID, false, 'PUBLISHED', $offset, $limit);

        if (sizeof($ids) > 0) {
            $this->log('Generating Graph for chunk ' . $this->chunkPos . ' of Data Source ' . $dsID . ' for ' . sizeof($ids) . ' records');
            try {
                $this->syncRO($ids);
            } catch (Exception $e) {
                throw new Exception('Error DSID:' . $dsID . ' Message: ' . $e->getMessage());
            }
        } else {
            $this->log('No records for data source: ' . $dsID);
        }
    }

    private function syncRO($ids){
        foreach ($ids as $id) {
            try {
                $record = $this->ci->ro->getByID($id);
                $this->addNode($record);
                $relatedObjects = $record->getAllRelatedObjects(false, false, true);
                foreach ($relatedObjects as $related) {
                    $relatedObject = $this->ci->ro->getByID($related['registry_object_id']);
                    $this->addNode($relatedObject);
                    $this->addRelationship($record, $related);
                    unset($relatedObject);
                }
                unset($record);
            } catch (Exception $e) {
                $this->log("Error generating graph for RO: ".$id.' -> '.$e->getMessage());
            }
        }
    }

    public function loadParams(){
        parse_str($this->params, $params);
        $this->target = isset($params['type']) ? $params['type'] : false;
        $this->target_id = isset($params['id']) ? $params['id'] : false;

        if (isset($params['chunkPos'])) {
            $this->chunkPos = $params['chunkPos'];
        }
    }

    /**
     * Returns a Neo4JClient that connects with the default config
     *
     * @param $neo4jConf
     */
    private function constructClient($neo4jConf)
    {
        $url = parse_url($neo4jConf['url']);
        $this->client = ClientBuilder::create()
            ->addConnection('default',
                $url['scheme'], $url['host'], $url['port'], true,
                $neo4jConf['username'], $neo4jConf['password'])
            ->setDefaultTimeout(20)
            ->setAutoFormatResponse(true)
            ->build();
    }


    public function addNode($record)
    {
        $recordTitle = addslashes($record->title);
        $recordKey = addslashes($record->key);
        $encodedRecordData = "{id: '$record->id', key: '$recordKey', title: '$recordTitle'}";
        $recordClass = ucfirst($record->class);
        $q = "MERGE (n:RegistryObject:$recordClass $encodedRecordData)";
        $q .= " RETURN n";
        if ($this->ci->input->get('debug')) {
            $this->log($q);
        }
        return $this->client->sendCypherQuery($q);
    }

    public function addRelationship($record, $related)
    {
        $relatedID = $related['registry_object_id'];
        $relationType = $related['relation_type'];
        $relationType = $this->camelCase($relationType);
        $q = "MATCH (n1:RegistryObject {id:'$record->id'}), (n2:RegistryObject {id:'$relatedID'})";
        $q .= " CREATE UNIQUE (n1)-[r:$relationType]-(n2)";
        $q .= " RETURN r";
        if ($this->ci->input->get('debug')) {
            $this->log($q);
        }
        $result = $this->client->sendCypherQuery($q);
        return $result;
    }

    public static function camelCase($str, array $noStrip = [])
    {
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        $str = lcfirst($str);
        return $str;
    }

}