<?php

namespace ANDS\Registry\ContentProviders\ISO;

use ANDS\Registry\ContentProvider\ContentProvider;

class ISO191153ContentProviderTest extends \RegistryTestClass
{



    public function test_loading_iso_content(){
        $native_content_path = __DIR__ ."../../../../../resources/harvested_contents/csw-iso-19115.xml";
        $xml = file_get_contents($native_content_path);
        $contentProvider = ContentProvider::getProvider('http://www.isotc211.org/2005/gmd', 'CSW');
        $contentProvider->loadContent($xml);
        $nativeObjects = $contentProvider->getContent();
        $this->assertTrue(sizeof($nativeObjects) == 54);
    }
}