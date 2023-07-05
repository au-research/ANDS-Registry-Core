<?php

namespace ANDS\Registry\Providers\HealthData;

use ANDS\File\Storage;
use ANDS\Registry\Providers\Quality\QualityMetadataProvider;

class HealthDataProviderTest extends \RegistryTestClass
{


    /**
     * @return void test data sharing statement
     */
    public function test_health_data_v2(){
        $xml = Storage::disk('test')->get('anzctr_xml/ACTRN12605000055606.xml');
        $actual = HealthDataProvider::getRelatedStudy($xml);
        $this->assertContains("Undecided", $actual);
    }

    public function test_health_data__empty_data_sharing_v2(){
        $xml = Storage::disk('test')->get('anzctr_xml/ACTRN12612000544875.xml');
        $actual = HealthDataProvider::getRelatedStudy($xml);
        $this->assertContains("Informed consent form,Ethical approval", $actual);
    }

    public function test_health_data_2_v2(){
        $xml = Storage::disk('test')->get('anzctr_xml/ACTRN12616000736448.xml');
        $actual = HealthDataProvider::getRelatedStudy($xml);
        $this->assertContains("data analyses will be considered on a case by case basis", $actual);
    }

    public function test_health_data_3_v2(){
        $xml = Storage::disk('test')->get('anzctr_xml/ACTRN12608000588392.xml');
        $actual = HealthDataProvider::getRelatedStudy($xml);
        $this->assertContains("All participant data collected", $actual);
    }

}