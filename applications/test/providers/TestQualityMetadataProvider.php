<?php


namespace ANDS\Test;

use ANDS\Registry\Providers\Quality\QualityMetadataProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class TestQualityMetadataProvider extends UnitTest
{
/**
 * Class TestRelationshipProvider
 * @package ANDS\Test
 */

    /** @test **/
    public function test_it_sould_delete_all_metadata() {
       // $collectionkey = 'AUTestingRecords3RelatedCollectionDatasetRelObj1';
        $collectionkey = 'AUTestingRecords3anudc:3317';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
        QualityMetadataProvider::deleteQualityAttributes($record);
        QualityMetadataProvider::deleteQualityMetadata($record);
        $quality_level = $record->getRegistryObjectAttributeValue('quality_info');
        $level_html = $record->getRegistryObjectAttributeValue('level_html');

        $this->assertNull($quality_level);
        $this->assertEquals($level_html, null);

        //QualityMetadataProvider::process($record);
    }
    /** @test **/
    public function test_it_sould_calculate_quality_level() {
        // $collectionkey = 'AUTestingRecords3RelatedCollectionDatasetRelObj1';
        $collectionkey = 'AUTestingRecordsQualityLevelsParty7';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
        QualityMetadataProvider::process($record);
        $quality_level = $record->getRegistryObjectAttributeValue('quality_level');
        //dd($quality_level->value);
        $this->assertEquals($quality_level, 3);

        //QualityMetadataProvider::process($record);
    }

    // php index.php test providers TestQualityMetadataProvider test_it_should_process_quality_for_all_records
    /** @test **/
    public function test_it_should_process_quality_for_all_records()
    {
        initEloquent();
        $records = RegistryObject::where('status', 'PUBLISHED')->where('data_source_id', 147)->get();

        foreach ($records as $record) {
            QualityMetadataProvider::process($record);
        }
    }
}