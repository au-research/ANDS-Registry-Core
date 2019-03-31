<?php

namespace ANDS\API\Task\ImportSubTask;
use \ANDS\Registry\Versions as Versions;
use \ANDS\Registry\Schema;
use \ANDS\RegistryObject\AltSchemaVersion;
use \ANDS\RegistryObject\RegistryObjectVersion;
use \ANDS\Repository\RegistryObjectsRepository;
use \DOMDocument;

class IngestNativeSchemaTest extends \RegistryTestClass
{
    /** @test */
    public function test_iso_extraction()
    {

        $importTask = new IngestNativeSchema();
        $dataSourceId = 14;
        $dom = new \DOMDocument();

        $xml = file_get_contents(__DIR__ ."../../../resources/harvested_contents/oaipmh.xml");
        // $xml = file_get_contents(__DIR__ ."../../../resources/harvested_contents/bom_csw.xml");
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
            try {
                $dom->loadXML($xml);
                $mdNodes = $dom->documentElement->getElementsByTagName('MD_Metadata');
                $errors = libxml_get_errors();
                if ($errors) {
                    foreach ($errors as $error) {
                        $this->print_load_error($error, $xml);
                    }
                } else {
                    $counter = 0;
                    foreach ($mdNodes as $mdNode) {
                        $success = $this->insertNativeObject($mdNode);
                        //if($success){
                            $counter++;
                        //}
                    }
                    $this->assertEquals(10, $counter);
                }
                libxml_clear_errors();
            }
            Catch(Exception $e){
                print("Errors while loading testFile Error message:". $e->getMessage());
            }


    }




    /**  @test */
    public function testPrefixGen(){

        $uriList  = array(
            "http://bluenet3.antcrc.utas.edu.au/mcp" => "http://bluenet3.antcrc.utas.edu.au/mcp",
            "iso2005gmd" => "http://www.isotc211.org/2005/gmd",
            "iso19115-3" => "http://standards.iso.org/iso/19115/-3/mdb/1.0"
        );

        foreach($uriList as $prefix=>$uri)
        {
            $this->assertEquals($prefix, Schema::getPrefix($uri));
        }


    }

    private function insertNativeObject($mdNode)
    {

        $dataSourceID = 14;
        $identifiers = [];
        $fileIdentifiers = $mdNode->getElementsByTagName('fileIdentifier');
        if (sizeof($fileIdentifiers) > 0) {
            foreach ($fileIdentifiers as $fileIdentifier) {
                $identifiers[] = ['identifier' => trim($fileIdentifier->nodeValue), 'identifier_type' => 'local'];
            }
        }

        if (sizeof($identifiers) == 0) {
            $mdIdentifiers = $mdNode->getElementsByTagName('MD_Identifier');
            if (sizeof($mdIdentifiers) > 0) {
                foreach ($mdIdentifiers as $mdIdentifier) {
                    if (sizeof($mdIdentifier->getElementsByTagName('codeSpace')) > 0) {
                        $cspElement = $mdIdentifier->getElementsByTagName('codeSpace')[0];
                        if ($cspElement != null && trim($cspElement->nodeValue) != '' && trim($cspElement->nodeValue) == 'urn:uuid') {
                            $identifiers[] = ['identifier' => trim($mdIdentifier->getElementsByTagName('code')[0]->nodeValue),
                                'identifier_type' => trim($cspElement->nodeValue)];
                        }
                    }
                }
            }
        }

        if (sizeof($identifiers) == 0) {
            echo "Couldn't determine Identifiers so quiting";
            return;
        }

        $schema = Schema::where('uri', $mdNode->namespaceURI)->first();

        if ($schema == null) {

            $schema = new Schema();
            $schema->setRawAttributes([
                'prefix' => Schema::getPrefix($mdNode->namespaceURI),
                'uri' => $mdNode->namespaceURI,
                'exportable' => 1
            ]);
            $schema->save();
        }

        $IdentifierArray = [];

        foreach ($identifiers as $identifier) {
            $IdentifierArray[] = $identifier['identifier'];
        }

        $this->assertGreaterThanOrEqual(1, sizeof($IdentifierArray));

        $registryObjects = RegistryObjectsRepository::getRecordsByIdentifier($IdentifierArray, $dataSourceID);

        $recordIDs = collect($registryObjects)->pluck('registry_object_id')->toArray();

        $dom = new DomDocument('1.0', 'UTF-8');
        $dom->appendChild($dom->importNode($mdNode, True));
        $data = $dom->saveXML();

        $hash = md5($data);

        foreach ($recordIDs as $id) {
            $existing = AltSchemaVersion::where('prefix', $schema->prefix)->where('registry_object_id', $id)->first();
            if (!$existing) {
                $existing = Versions::create([
                    'data' => $data,
                    'hash' => $hash,
                    'origin' => 'HARVESTER',
                    'schema_id' => $schema->id,
                ]);
            } elseif ($hash != $existing->version->hash) {
                $existing->version->update([
                    'data' => $data,
                    'hash' => $hash
                ]);
            }

            RegistryObjectVersion::firstOrCreate([
                'version_id' => $existing->id,
                'registry_object_id' => $id
            ]);
        }
    }


        function print_load_error($error, $xml)
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

            print("Errors while loading  Test file  Error message:". $error_msg);
        }
}