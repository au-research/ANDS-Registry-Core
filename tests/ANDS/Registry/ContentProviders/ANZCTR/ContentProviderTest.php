<?php

namespace Registry\ContentProvider\ANZCTR;

use ANDS\Registry\ContentProvider\ANZCTR\ContentProvider;
use PHPUnit\Framework\TestCase;

class ContentProviderTest extends TestCase
{
    /**
     * test the provider
     * @return void
     */
/*    public function test_provider(){
        $cp = new ContentProvider();
        $cp->get("ACTRN12612000544875");
        $content = $cp->getContent();
        $this->assertTrue(str_contains($content , 'ACTRN12612000544875'));
        $index = $cp->getIndexableArray();
        $this->assertTrue(sizeof($index) > 9);
    }

    public function test_provider_no_result(){
        $cp = new ContentProvider();
        $cp->get("BLUEY");
        $content = $cp->getContent();
        $this->assertFalse(str_contains($content , '<trial>'));

    }*/
}
