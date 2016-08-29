<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportTask;
use ANDS\RecordData;
use ANDS\Util\XMLUtil;


class TestIngestTask extends UnitTest
{

    /** @test **/
    public function test_it_should_ingest_the_one_record()
    {
        $task = $this->getIngestTask();
        $this->runPrerequisite($task->parent());
        $task->run();

        // dd(XMLUtil::countElementsByName($task->parent()->getFirstPayload(), 'registryObject'));
        dd($task->toArray());
    }

    public function getIngestTask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecords'
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
        RecordData::where('hash', 'c52218f2ed0b1bb623a5f99e6f0d97bb')->delete();
        RecordData::find(9875063)->update(['current' => 'TRUE']);
    }
}