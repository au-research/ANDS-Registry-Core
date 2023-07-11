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


    /* @test */
    public function test_anzctr_pullbackV2(){
        $md = ANZCTRUtil::retrieveMetadataV2("ACTRN12612000544875");
        $this->assertTrue(str_contains($md, "<trial>"));
    }

    public function test_anzctr_pullback_id_onlyV2(){
        $md = ANZCTRUtil::retrieveMetadataV2("12612000544875");
        $this->assertTrue(str_contains($md, "<trial>"));
    }

    public function test_anzctr_pullback_id_only_shorter_than_14_digit_idV2(){
        $this->setExpectedException(\Exception::class);
        ANZCTRUtil::retrieveMetadataV2("1261200054487");
    }


    public function test_anzctr_pullback_shorter_than_14_digit_idV2(){
        $this->setExpectedException(\Exception::class);
        ANZCTRUtil::retrieveMetadataV2("ACTRN126120005");
    }

    public function test_nullV2(){
        $this->setExpectedException(\Exception::class);
        ANZCTRUtil::retrieveMetadataV2(null);
    }
}
