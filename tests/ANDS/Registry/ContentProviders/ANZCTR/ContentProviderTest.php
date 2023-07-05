<?php

namespace Registry\ContentProvider\ANZCTR;

use ANDS\Registry\ContentProvider\ANZCTR\ContentProvider;
use ANDS\Util\ANZCTRUtil;
use DOMDocument;
use PHPUnit\Framework\TestCase;

class ContentProviderTest extends TestCase
{
    /**
     * test the provider
     * @return void
     */

    public function test_until_we_get_a_mockservice_for_anzctr()
    {
        $this->assertTrue(true);
    }
/*
    public function test_provider()
    {
        $cp = new ContentProvider();
        $xml = ANZCTRUtil::retrieveMetadata("ACTRN12612000544875");
        $dom = new DOMDocument;
        $dom->loadXML($xml);
        $index = $cp::getIndex($dom, "url_of:ACTRN12612000544875", "ACTRN12612000544875");
        $this->assertTrue(sizeof($index) > 5);
    }

    public function test_v2_provider()
    {
        $cp = new ContentProvider();
        $xml = ANZCTRUtil::retrieveMetadataV2("ACTRN12612000544875");
        $dom = new DOMDocument;
        $dom->loadXML($xml);
        $index = $cp::getIndex($dom, "url_of:ACTRN12612000544875", "ACTRN12612000544875");
        var_dump($index);
        $this->assertTrue(sizeof($index) > 5);
    }
*/
}

