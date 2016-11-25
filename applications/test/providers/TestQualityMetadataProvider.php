<?php


namespace ANDS\Test;

use ANDS\Registry\Providers\QualityMetadataProvider;
use ANDS\Repository\RegistryObjectsRepository;

class TestQualityMetadataProvider extends UnitTest
{
/**
 * Class TestRelationshipProvider
 * @package ANDS\Test
 */

    public function test_it_sould_delete_all_metadata(){
        $collectionkey = 'AUTestingRecords3anudc:3317';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);

        QualityMetadataProvider::process($record);
    }
}