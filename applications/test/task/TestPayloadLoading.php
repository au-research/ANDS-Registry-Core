<?php


namespace ANDS\Test;

use ANDS\API\Task\ImportTask;
use ANDS\Payload;

class TestPayloadLoading extends UnitTest
{
    /** @test **/
    public function test_it_should_load_payload_for_a_file_without_validated()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->initialiseTask();

        $payloadInfo = array_first($importTask->getTaskData("payloadsInfo"));
        $this->assertTrue(array_key_exists('path', $payloadInfo));

        $payload = new Payload($payloadInfo['path']);

        $content = $payload->getContentByStatus('unvalidated');
        $this->assertTrue($content);

    }

    /** @test **/
    public function test_it_should_load_payload_for_a_file_with_validated_status()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=PayloadTest'
        ])->initialiseTask();

        $payloadInfo = array_first($importTask->getTaskData("payloadsInfo"));
        $this->assertTrue(array_key_exists('path', $payloadInfo));
        $this->assertTrue(array_key_exists('path_validated', $payloadInfo));
        $this->assertTrue(array_key_exists('path_processed', $payloadInfo));

        $payload = new Payload($payloadInfo['path']);
        $content = $payload->getContentByStatus('unvalidated');
        $this->assertTrue($content);
        $content = $payload->getContentByStatus('validated');
        $this->assertTrue($content);
        $content = $payload->getContentByStatus('processed');
        $this->assertTrue($content);
    }

    /** @test **/
    public function test_it_should_load_payload_for_a_directory()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=PayloadTestDir'
        ])->initialiseTask();

        $payloadInfo = $importTask->getTaskData("payloadsInfo");
        $this->assertEquals(2, count($payloadInfo));

        $payloadInfo = array_first($importTask->getTaskData("payloadsInfo"));
        $this->assertTrue(array_key_exists('path', $payloadInfo));
        $this->assertTrue(array_key_exists('path_validated', $payloadInfo));
        $this->assertTrue(array_key_exists('path_processed', $payloadInfo));

        $payload = new Payload($payloadInfo['path']);
        $content = $payload->getContentByStatus('unvalidated');
        $this->assertTrue($content);
        $content = $payload->getContentByStatus('validated');
        $this->assertTrue($content);
        $content = $payload->getContentByStatus('processed');
        $this->assertTrue($content);
    }

}