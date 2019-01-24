<?php

namespace ANDS\API\Task\ImportSubTask;

use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use ANDS\Repository\DataSourceRepository;
use ANDS\RegistryObject\AltSchemaVersion;
use ANDS\Repository\RegistryObjectsRepository as Repo;
use SimpleXMLElement;

class IngestNativeSchema extends ImportSubTask
{
    protected $requirePayload = true;
    protected $title = "INGESTING NATIVE CONTENT RECORDS";
    protected $data_source = null;

    public function run_task()
    {
        $payloads = $this->parent()->loadPayload('tmp')->getPayloads();
        $multiplePayloads = count($payloads) > 1 ? true : false;
        $this->data_source = DataSourceRepository::getByID($this->parent()->dataSourceID);
        $payloadCounter = 0;
        foreach ($this->parent()->getPayloads() as $payloadIndex => $payload) {
            $payloadCounter++;
            $xml = $payload->getContentByStatus('original');
            if ($xml === null) {
                $this->addError("No Original content were found for ". $payload->getPath());
                break;
            }

            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $mdNodes = $dom->documentElement->getElementsByTagName('MD_Metadata');


            foreach ($mdNodes as $mdNode) {
                $namespaceURI = 'http://standards.iso.org/iso/19115/-3/mdb/1.0';
                echo $mdNode->nodeName;
                echo $mdNode->namespaceURI;
                $identifiers = $dom->documentElement->getElementsByTagName('MD_Metadata');
                //$schema = $importTask::getNameSpaceFromSimpleXML($mdNode);
                //echo $schema;
            }

            if ($multiplePayloads) {
                $this->updateProgress(
                    $payloadCounter, count($payloads),
                    "Processed payload ($payloadCounter/".count($payloads).") " . $payloadIndex
                );
            }
        }

        $this->parent()->updateHarvest([
            "importer_message" => "Records Created: ".$payloadCounter
        ]);

    }

    /*Insert a record in alt_schema_versions
     *
     * @param nativeObject
     *
     */
    public function insertNativeObject($nativeObject)
    {
        if(!static::isValid($nativeObject))
            return false;

        $xml = $nativeObject->saveXML();

        if ($existing = AltSchemaVersion::where('registry_object_id', $record->id)->where('schema', static::$schema)->first()) {
            $existing->data = $xml;
            $existing->hash = md5($xml);
            $existing->updated_at = date("Y-m-d G:i:s");
            $existing->save();
            return true;
        }
        $schema = XMLUtil::getSchema($xml);
        $schema = XMLUtil::getSchema($xml);
        $new = new AltSchemaVersion;
        $new->setRawAttributes([
            'data' => $xml,
            'hash' => md5($xml),
            'schema' => $schema,
            'registry_object_id' => $record->id,
            'registry_object_group' => $record->group,
            'registry_object_key' => $record->key,
            'registry_object_data_source_id' => $record->data_source_id,
            'updated_at' => date("Y-m-d G:i:s")
        ]);
        $new->save();

        return true;
    }


    public static function getNameSpaceFromSimpleXML($mdNode)
    {
        var_dump($mdNode);
    }

    /**
     * @param $sxml
     * @param $xpath
     * @return mixed
     */
    public static function getElementsByXPathFromSXML(\SimpleXMLElement $sxml, $xpath)
    {
        return $sxml->xpath($xpath);
    }

}