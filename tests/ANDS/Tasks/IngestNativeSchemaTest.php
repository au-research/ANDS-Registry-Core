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
        $dom->load("/var/www/html/workareas/leo/registry/tests/resources/harvested_contents/oaipmh.xml");
        //$dom->load("/var/www/html/workareas/leo/registry/tests/resources/harvested_contents/csw.xml");
        $mdNodes = $dom->documentElement->getElementsByTagName('MD_Metadata');


        foreach ($mdNodes as $mdNode) {
            $this->insertNativeObject($mdNode);
        }


    }

    /**  @test */
    public function testPrefixGen(){

        $uriList  = array(
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

        $registryObjects = RegistryObjectsRepository::getRecordsByIdentifier($IdentifierArray, $dataSourceID);

        $recordIDs = collect($registryObjects)->pluck('registry_object_id')->toArray();

        $dom = new DomDocument('1.0', 'UTF-8');
        $dom->appendChild($dom->importNode($mdNode, True));
        $data = $dom->saveXML();

        $hash = md5($data);

        foreach ($recordIDs as $id) {
            $existing = AltSchemaVersion::where('prefix', $schema->prefix)->where('registry_object_id', $id)->first();
var_dump($hash);
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
}