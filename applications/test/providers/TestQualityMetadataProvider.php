<?php


namespace ANDS\Test;

use ANDS\Registry\Providers\QualityMetadataProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class TestQualityMetadataProvider extends UnitTest
{
/**
 * Class TestRelationshipProvider
 * @package ANDS\Test
 */

    public function test_it_sould_delete_all_metadata() {
        $collectionkey = 'AUTestingRecords3RelatedCollectionDatasetRelObj1';
        $collectionkey = 'AUTestingRecords3anudc:3317';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);

        QualityMetadataProvider::process($record);
    }

    // php index.php test providers TestQualityMetadataProvider test_it_should_process_quality_for_all_records
    /** @test **/
    public function test_it_should_process_quality_for_all_records()
    {
        initEloquent();
        $records = RegistryObject::where('status', 'PUBLISHED')->where('data_source_id', 205)->get();

        foreach ($records as $record) {
            QualityMetadataProvider::process($record);
        }
    }
}