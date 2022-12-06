<?php

namespace Util;

use ANDS\Util\ANZCTRUtil;
use PHPUnit\Framework\TestCase;

class ANZCTRUtilTest extends TestCase
{

    /* @test */
    public function test_anzctr_pullback(){
    $md = ANZCTRUtil::retrieveMetadata("ACTRN12612000544875");
    $this->assertTrue(str_contains($md, "<trial>"));
    }

}
