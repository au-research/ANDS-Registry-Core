<?php


namespace ANDS\Test;

use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class TestProcessDeleteTask extends UnitTest
{
    /** @test **/
    public function test_it_should_set_record_as_deleted()
    {
        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertTrue(RegistryObjectsRepository::deleteRecord($record->registry_object_id));

        $record = RegistryObject::where('key', 'minh-test-record-pipeline')->first();
        $this->assertEquals("DELETED", $record->status);
    }

    public function setUp()
    {
        require_once(API_APP_PATH.'vendor/autoload.php');
    }
}