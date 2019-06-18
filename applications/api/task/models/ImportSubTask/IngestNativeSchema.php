<?php

namespace ANDS\API\Task\ImportSubTask;

use \ANDS\Registry\Versions as Versions;
use \ANDS\Registry\Schema;
use \ANDS\RegistryObject\RegistryObjectVersion;
use \ANDS\Repository\RegistryObjectsRepository;
use \ANDS\Repository\DataSourceRepository;
use \DOMDocument;

class IngestNativeSchema extends ImportSubTask
{
    protected $requirePayload = true;
    protected $title = "INGESTING NATIVE CONTENT RECORDS";
    protected $data_source = null;
    protected $payloadSource = "native";

    public function run_task()
    {
        if (!$this->parent()->hasPayloadExtension('tmp')) {
            $this->log("No native schema exists");
            return;
        }

        $payloads = $this->parent()->loadPayload('tmp')->getPayloads();
        $multiplePayloads = count($payloads) > 1 ? true : false;
        $this->data_source = DataSourceRepository::getByID($this->parent()->dataSourceID);
        $payloadCounter = 0;
        foreach ($this->parent()->getPayloads() as $payloadIndex => $payload) {

            $xml = $payload->getContentByStatus($this->payloadSource);
            if ($xml === null) {
                $this->addError("No Original content were found for ". $payload->getPath());
                break;
            }
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            try{
                $dom->loadXML($xml);
                $mdNodes = $dom->documentElement->getElementsByTagName('MD_Metadata');
                $errors = libxml_get_errors();
                if($errors) {
                    foreach ($errors as $error) {
                        $this->add_load_error($error, $xml);
                    }
                }
                else{
                    foreach ($mdNodes as $mdNode) {
                        $success = $this->insertNativeObject($mdNode);
                        if($success)
                            $payloadCounter++;
                    }
                }
                libxml_clear_errors();
            }
            Catch(Exception $e){
                $this->addError("Errors while loading  ".$this->payloadSource. " Error message:". $e->getMessage());
            }


            if ($multiplePayloads) {
                $this->updateProgress(
                    $payloadCounter, count($payloads),
                    "Processed Native ($payloadCounter/".count($payloads).") " . $payloadIndex
                );
            }
        }

        $this->parent()->updateHarvest([
            "importer_message" => "Records Created: ".$payloadCounter
        ]);
        $this->parent()->setTaskData("NativeObjectsCreated", $payloadCounter);

    }

    function add_load_error($error, $xml)
    {
        $error_msg  = $xml[$error->line - 1] . "\n";
        $error_msg.= str_repeat('-', $error->column) . "^\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $error_msg .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $error_msg .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $error_msg .= "Fatal Error $error->code: ";
                break;
        }

        $error_msg .= trim($error->message) .
            "\n  Line: $error->line" .
            "\n  Column: $error->column";

        if ($error->file) {
            $error_msg .= "\n  File: $error->file";
        }

        $this->addError("Errors while loading  ".$this->payloadSource. " Error message:". $error_msg);
    }


    /*Insert a record versions
     *
     * @param nativeObject DomElement
     *
     */
    private function insertNativeObject($mdNode)
    {

        $identifiers = [];
        $created = false;
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
            $this->log("Couldn't determine Identifiers");
            return $created;
        }

        $schema = Schema::where('uri', $mdNode->namespaceURI)->first();

        if($schema == null){

            $schema = new Schema();
            $schema->setRawAttributes([
                'prefix' => Schema::getPrefix($mdNode->namespaceURI),
                'uri' => $mdNode->namespaceURI,
                'exportable' => 1
            ]);
            $schema->save();
        }

        $IdentifierArray = [];

        foreach($identifiers as $identifier) {
            $IdentifierArray[] =  $identifier['identifier'];
        }

        $registryObjects = RegistryObjectsRepository::getRecordsByIdentifier($IdentifierArray, $this->parent()->dataSourceID);

        $recordIDs = collect($registryObjects)->pluck('registry_object_id')->toArray();

        $dom = new DomDocument('1.0', 'UTF-8');
        $dom->appendChild($dom->importNode($mdNode, True));
        $data = $dom->saveXML($dom->documentElement);

        $hash = md5($data);

        foreach ($recordIDs as $id) {

            $altVersionsIDs = RegistryObjectVersion::where('registry_object_id', $id)->get()->pluck('version_id')->toArray();
            $existing = null;
            if (count($altVersionsIDs) > 0) {
                $existing = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $schema->id)->first();
            }

            if (!$existing) {
                $version = Versions::create([
                    'data' => $data,
                    'hash' => $hash,
                    'origin' => 'HARVESTER',
                    'schema_id' => $schema->id,
                ]);
                RegistryObjectVersion::firstOrCreate([
                    'version_id' => $version->id,
                    'registry_object_id' => $id
                ]);
            } elseif ($hash != $existing->hash) {
                $existing->update([
                    'data' => $data,
                    'hash' => $hash
                ]);
            }

            $created = true;
        }
        return $created;
    }

}