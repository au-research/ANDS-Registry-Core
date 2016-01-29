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
    public function run_task()
    {
        $this->client = ClientBuilder::create()
            ->addConnection('default', 'http', 'minhdev.ands.org.au', 7474, true, 'neo4j', 'abc123')
            ->setDefaultTimeout(20)
            ->setAutoFormatResponse(true)
            ->build();

        $this->ci =& get_instance();
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $this->ci->load->model('registry/data_source/data_sources', 'ds');


        $ids = $this->ci->ro->getIDsByDataSourceID(162, false, 'PUBLISHED');
        foreach ($ids as $id) {
            $this->sync($id);
        }

    }

    public function sync($recordID)
    {
        $record = $this->ci->ro->getByID($recordID);

        $this->addNode($record);

        $relatedObjects = $record->getAllRelatedObjects(false, false, true);
        foreach ($relatedObjects as $related) {
            $relatedObject = $this->ci->ro->getByID($related['registry_object_id']);
            $this->addNode($relatedObject);
            $this->addRelationship($record, $related);
            unset($relatedObject);
        }
    }

    public function addNode($record){
        $recordTitle = addslashes($record->title);
        $encodedRecordData = "{id: '$record->id', key: '$record->key', title: '$recordTitle'}";
        $recordClass = ucfirst($record->class);
        $q = "MERGE (n:RegistryObject:$recordClass $encodedRecordData)";
        $q .= " RETURN n";
        return $this->client->sendCypherQuery($q);
    }

    public function addRelationship($record, $related){
        $relatedID = $related['registry_object_id'];
        $relationType = $related['relation_type'];
        $relationType = $this->camelCase($relationType);
        $q = "MATCH (n1:RegistryObject {id:'$record->id'}), (n2:RegistryObject {id:'$relatedID'})";
        $q .= " CREATE UNIQUE (n1)-[r:$relationType]-(n2)";
        $q .= " RETURN r";
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