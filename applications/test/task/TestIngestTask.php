<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportTask;
use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;


class TestIngestTask extends UnitTest
{

    /** @test **/
    public function test_it_should_ingest_the_one_record()
    {
        $task = $this->getIngestTask();
        $this->runPrerequisite($task->parent());
        $task->run();

        $record1 = RegistryObject::where('key', 'AUTestingRecords3h-dataset-31')->first();
        $this->assertInstanceOf($record1, RegistryObject::class);

        // TODO: check more stuffs
    }

    public function getIngestTask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecords3'
        ])->setCI($this->ci)->initialiseTask();
        $task = $importTask->getTaskByName("Ingest");
        return $task;
    }

    public function runPrerequisite($importTask)
    {
        $populateImportTask = $importTask->getTaskByName("PopulateImportOptions");
        $populateImportTask->run();
        $validateTask = $importTask->getTaskByName("ValidatePayload");
        $validateTask->run();
        $processTask = $importTask->getTaskByName("ProcessPayload");
        $processTask->run();
    }

    public function setUp()
    {
        require_once(API_APP_PATH.'vendor/autoload.php');
    }

    public function tearDown()
    {
        // delete record data with hash c52218f2ed0b1bb623a5f99e6f0d97bb
        // @todo make sure this runs
//        RecordData::where('hash', 'c52218f2ed0b1bb623a5f99e6f0d97bb')->first()->delete();
//        RecordData::find(9875063)->update(['current' => 'TRUE']);

        $records = ['AUTestingRecords3h-dataset-31', 'AUTestingRecords3h-dataset-33', 'AUTestingRecords3h-dataset-36'];
        foreach ($records as $key) {
            if ($record = RegistryObject::where('key', $key)->first()) {
//                RecordData::where('registry_object_id', $record->registry_object_id)->delete();
                $record->delete();
            }
        }
    }
}