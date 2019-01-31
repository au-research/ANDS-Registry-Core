<?php

namespace ANDS\API\Task\ImportSubTask;

use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use ANDS\Repository\DataSourceRepository;
use ANDS\RegistryObject\AltSchemaVersion;
use ANDS\Repository\RegistryObjectsRepository as Repo;
use SimpleXMLElement;
use ANDS\Registry\VersionsIdentifiers;
use \ANDS\Registry\Versions as Versions;
use \ANDS\Registry\Schema;
use \DOMDocument;

class IngestNativeSchema extends ImportSubTask
{
    protected $requirePayload = true;
    protected $title = "INGESTING NATIVE CONTENT RECORDS";
    protected $data_source = null;
    protected $payloadSource = "native";

    public function run_task()
    {
        $payloads = $this->parent()->loadPayload('tmp')->getPayloads();
        $multiplePayloads = count($payloads) > 1 ? true : false;
        $this->data_source = DataSourceRepository::getByID($this->parent()->dataSourceID);
        $payloadCounter = 0;
        foreach ($this->parent()->getPayloads() as $payloadIndex => $payload) {
            $payloadCounter++;
            $xml = $payload->getContentByStatus($this->payloadSource);
            if ($xml === null) {
                $this->addError("No Original content were found for ". $payload->getPath());
                break;
            }

            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $mdNodes = $dom->documentElement->getElementsByTagName('MD_Metadata');

            foreach ($mdNodes as $mdNode) {
                $identifiers = [];
                $existingVersionIds = [];
                $fileIdentifiers = $mdNode->getElementsByTagName('fileIdentifier');
                if(sizeof($fileIdentifiers) > 0){
                    foreach ($fileIdentifiers as $fileIdentifier){
                        $identifiers[] = ['identifier' => trim($fileIdentifier->nodeValue), 'identifier_type' => 'local'];
                    }
                }

                if(sizeof($identifiers) == 0){
                    $mdIdentifiers = $mdNode->getElementsByTagName('MD_Identifier');
                    if(sizeof($mdIdentifiers) > 0){
                        foreach ($mdIdentifiers as $mdIdentifier){
                            if(sizeof($mdIdentifier->getElementsByTagName('codeSpace')) > 0) {
                                $cspElement = $mdIdentifier->getElementsByTagName('codeSpace')[0];
                                if ($cspElement != null && trim($cspElement->nodeValue) != '' && trim($cspElement->nodeValue) == 'urn:uuid') {
                                    $identifiers[] = ['identifier' => trim($mdIdentifier->getElementsByTagName('code')[0]->nodeValue),
                                        'identifier_type' => trim($cspElement->nodeValue)];
                                }
                            }
                        }
                    }
                }

                if(sizeof($identifiers) == 0){
                    echo "Couldn't determine Identifiers so quiting";
                    break;
                }

                foreach($identifiers as $identifier) {
                    $existingVersionIds = VersionsIdentifiers::where('identifier', $identifier['identifier'])
                        ->where('identifier_type', $identifier['identifier_type'])->pluck('version_id');
                }

                Versions::wherein('id', $existingVersionIds)->delete();
                VersionsIdentifiers::wherein('version_id', $existingVersionIds)->delete();

                $newVersionId = $this->insertNativeObject($mdNode);

                foreach($identifiers as $identifier) {
                    $versionIdentifier = new VersionsIdentifiers(['version_id' => $newVersionId,
                        'identifier' => $identifier['identifier'],
                        'identifier_type' => $identifier['identifier_type']]);
                    $versionIdentifier->save();
                }
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

    /*Insert a record versions
     *
     * @param nativeObject DomElement
     *
     */
    private function insertNativeObject($nativeObject)
    {


        $schema = Schema::where('uri', $nativeObject->namespaceURI)->first();
        // check if the schema exists, if not, create it
        if($schema == null){

            $schema = new Schema();
            $schema->setRawAttributes([
                'prefix' => Schema::getPrefix($nativeObject->namespaceURI),
                'uri' => $nativeObject->namespaceURI,
                'exportable' => 1
            ]);
            $schema->save();
        }


        $newVersion = new Versions();
        $dom = new DomDocument('1.0', 'UTF-8');
        $dom->appendChild($dom->importNode($nativeObject, True));
        $data = $dom->saveXML();
        $newVersion->setRawAttributes([
            'data' => $data,
            'hash' => md5($data),
            'origin' => 'HARVESTER',
            'schema_id' => $schema->id,
            'updated_at' => date("Y-m-d G:i:s")
        ]);

        $newVersion->save();

        return $newVersion->id;
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