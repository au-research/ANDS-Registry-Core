<?php

namespace ANDS\API\Task\ImportSubTask;
use \ANDS\Registry\Providers\ISO19115\ISO19115_3Provider;
use ANDS\Registry\VersionsIdentifiers;
use \ANDS\Registry\Versions as Versions;
use \ANDS\Registry\Schema;
use \DOMDocument;

class IngestNativeSchemaTest extends \RegistryTestClass
{
    /** @test */
    public function test_iso_extraction()
    {

        $importTask = new IngestNativeSchema();

        $dom = new \DOMDocument();
        $dom->load("/var/www/html/workareas/leo/registry/tests/resources/harvested_contents/oaipmh.xml");
        //$dom->load("/var/www/html/workareas/leo/registry/tests/resources/harvested_contents/csw.xml");
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

            foreach($identifiers as $identifier) {
                $existingVersionIds = VersionsIdentifiers::where('identifier', $identifier['identifier'])
                ->where('identifier_type', $identifier['identifier_type'])->pluck('version_id');
            }

            Versions::wherein('id', $existingVersionIds)->delete();
            VersionsIdentifiers::wherein('version_id', $existingVersionIds)->delete();

            $newVersion = new \ANDS\Registry\Versions();
            $dom = new DomDocument('1.0', 'UTF-8');
            $dom->appendChild($dom->importNode($mdNode, True));
            $data = $dom->saveXML();
            $newVersion->setRawAttributes([
                'data' => $data,
                'hash' => md5($data),
                'origin' => 'HARVESTER',
                'schema_id' => $schema->id,
                'updated_at' => date("Y-m-d G:i:s")
            ]);
            $newVersion->save();
            // check if the schema exists, if not, create it
            foreach($identifiers as $identifier) {
                $versionIdentifier = new VersionsIdentifiers(['version_id' => $newVersion->id,
                    'identifier' => $identifier['identifier'],
                    'identifier_type' => $identifier['identifier_type']]);
                $versionIdentifier->save();
            }
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

}