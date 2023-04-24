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

    public function test_anzctr_pullback_id_only(){
        $md = ANZCTRUtil::retrieveMetadata("12612000544875");
        $this->assertTrue(str_contains($md, "<trial>"));
    }

    public function test_anzctr_pullback_id_only_shorter_than_14_digit_id(){
        $this->setExpectedException(\Exception::class);
        ANZCTRUtil::retrieveMetadata("1261200054487");
    }


    public function test_anzctr_pullback_shorter_than_14_digit_id(){
        $this->setExpectedException(\Exception::class);
        ANZCTRUtil::retrieveMetadata("ACTRN126120005");
    }

    public function test_null(){
        $this->setExpectedException(\Exception::class);
        ANZCTRUtil::retrieveMetadata(null);
    }
}
