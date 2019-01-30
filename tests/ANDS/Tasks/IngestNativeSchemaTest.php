<?php

namespace ANDS\API\Task\ImportSubTask;
use \ANDS\Registry\Providers\ISO19115\ISO19115_3Provider;
use ANDS\Util\XMLUtil;

class IngestNativeSchemaTest extends \RegistryTestClass
{
    /** @test */
    public function test_iso_extraction()
    {

        $importTask = new IngestNativeSchema();

        $dom = new \DOMDocument();
        //$dom->load("/var/www/html/workareas/leo/registry/tests/resources/harvested_contents/oaipmh.xml");
        $dom->load("/var/www/html/workareas/leo/registry/tests/resources/harvested_contents/csw.xml");
        $mdNodes = $dom->documentElement->getElementsByTagName('MD_Metadata');


        foreach ($mdNodes as $mdNode) {
            $identifier = '';
            echo $mdNode->nodeName;
            echo $mdNode->namespaceURI;

            $fileIdentifiers = $mdNode->getElementsByTagName('fileIdentifier');
            if(sizeof($fileIdentifiers) > 0){
                foreach ($fileIdentifiers as $fileIdentifier){
                    $identifier = $fileIdentifier->nodeValue;
                    break;
                }
            }

            if($identifier == ''){
                $mdIdentifiers = $mdNode->getElementsByTagName('MD_Identifier');
                if(sizeof($mdIdentifiers) > 0){
                    foreach ($mdIdentifiers as $mdIdentifier){
                        $identifier = $mdIdentifier->getElementsByTagName('code')[0]->nodeValue;
                        break;
                    }
                }
            }
            
            var_dump(trim($identifier));
            echo '\n';
        }


    }

}