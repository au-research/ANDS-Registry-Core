<?php

namespace ANDS\Test;


use ANDS\API\Task\ImportSubTask\ImportSubTask;
use ANDS\API\Task\ImportTask;
use ANDS\DataSource;
use ANDS\Payload;
use ANDS\Repository\DataSourceRepository;
use ANDS\Util\XMLUtil;

/**
 * Class TestValidatePayload
 * @package ANDS\Test
 */
class TestValidatePayload extends UnitTest
{
    /** @test * */
    public function test_it_should_be()
    {
        $importTask = $this->getImportTask();
        $task = $importTask->getTaskByName("ValidatePayload");
        $this->assertEquals("ValidatePayload", $task->name);
        $this->assertEquals("PENDING", $task->status);
    }

    /** @test **/
    public function test_it_should_load_payload_to_parent_task()
    {
        $importTask = $this->getImportTask();
        $task = $importTask->getTaskByName("ValidatePayload");
        $payload = $task->parent()->getPayloads();

        $this->assertTrue(is_array($payload));
        $this->assertTrue(count($payload) > 0);
        $this->assertTrue(count(array_first($payload)) > 0);
    }

    /** @test **/
    public function test_it_should_validate_rifcs_xml()
    {
        $importTask = $this->getImportTask();
        $task = $importTask->getTaskByName("ValidatePayload");
        $task->run();

        // check that validated file get generated correctly
        $validatedFilePath = TEST_APP_PATH . 'core/data/209/AUTestingRecords_ds209_10_different_records.xml.validated';
        $this->assertTrue(file_exists($validatedFilePath));

        $payloads = $task->parent()->getPayloads();
        $thePayload = array_first($payloads);

        $this->assertArrayHasKey('path_validated', $thePayload->toArray());

        // clean up after
        unlink($validatedFilePath);
    }

    /** @test */
    public function test_it_should_validate_rifcs_xml_but_remove_invalidated_ones()
    {
        $this->ci->config->set_item('harvested_contents_path', TEST_APP_PATH . 'core/data/');
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'Has 15 records, only 13 validated',
            'params' => http_build_query([
                'ds_id' => 209,
                'batch_id' => 'AUTestingRecords'
            ])
        ])->initialiseTask();
        $task = $importTask->getTaskByName("ValidatePayload");

        $payloadInfo = array_first($task->parent()->getTaskData('payloadsInfo'));

        $payload = new Payload($payloadInfo['path']);

        $xml = $payload->getContentByStatus('unvalidated');
        $this->assertEquals(15, XMLUtil::countElementsByName($xml, 'registryObject'));

        $task->run();

        $payload->init();
        $xml = $payload->getContentByStatus('validated');
        $this->assertEquals(13, XMLUtil::countElementsByName($xml, 'registryObject'));

        $this->assertRegExp("/The attribute 'type' is required but missing./", $importTask->getError());

        // clean up after
        unlink(TEST_APP_PATH . 'core/data/209/AUTestingRecords.xml.validated');
    }

    /** @test **/
    public function test_it_should_return_when_no_payload_provided()
    {
        $this->ci->config->set_item('harvested_contents_path', TEST_APP_PATH . 'core/data/');
        $importTask = new ImportTask;
        $importTask->init([
            "name" => "This import task should fail gracefully",
            'params' => http_build_query([
                'ds_id' => 209,
                'batch_id' => 'NonExistentFile'
            ])
        ])->initialiseTask();
        $task = $importTask->getTaskByName("ValidatePayload");

        $task->run();
        $this->assertGreaterThanOrEqual(count($task->parent()->getError()), 1);
    }

    /** @test **/
    public function test_it_should_handle_invalid_doc_correctly()
    {
        $this->ci->config->set_item('harvested_contents_path', TEST_APP_PATH . 'core/data/');
        $importTask = new ImportTask;
        $importTask->init([
            "name" => "This import task should fail gracefully",
            'params' => http_build_query([
                'ds_id' => 209,
                'batch_id' => 'Invalid_XML_Document'
            ])
        ])->initialiseTask();

        $importTask->enableRunAllSubTask();

        $importTask->run();
        $this->assertEquals("STOPPED", $importTask->getStatus());

        $this->assertRegExp("/XML does not pass validation/", $importTask->getError());
        $this->assertTrue($importTask->hasError());


    }

    /** @test **/
    public function test_it_should_handle_no_document_correctly()
    {
        $this->ci->config->set_item('harvested_contents_path', TEST_APP_PATH . 'core/data/');
        $importTask = new ImportTask;
        $importTask->init([
            "name" => "This import task should fail gracefully",
            'params' => http_build_query([
                'ds_id' => 209,
                'batch_id' => 'No_Document'
            ])
        ])->initialiseTask();

        $importTask->enableRunAllSubTask();

        $importTask->run();

        $this->assertFalse($importTask->hasError());

        // clean up after
        unlink(TEST_APP_PATH . 'core/data/209/No_Document.xml.validated');
        unlink(TEST_APP_PATH . 'core/data/209/No_Document.xml.processed');
    }

    /**
     * Helper
     * Return an ImportSubTask for ValidatePayload for use each test
     * dataSourceID is hard coded to 209 for testing only
     *
     * @return ImportTask
     */
    private function getImportTask()
    {
        $this->ci->config->set_item('harvested_contents_path', TEST_APP_PATH . 'core/data/');
        $importTask = new ImportTask();
        $importTask->init([
            'params' => http_build_query([
                'ds_id' => 209,
                'batch_id' => 'AUTestingRecords_ds209_10_different_records'
            ])
        ])->initialiseTask();

        return $importTask;
    }


    public function setUpBeforeClass()
    {
        $importTask = new ImportTask();
        $importTask->initialiseTask();
    }

    public function tearDownAfterClass()
    {
        $fileToRemove = ['No_Document.xml.processed', 'No_Document.xml.validated'];
        foreach ($fileToRemove as $file) {
            if (file_exists(TEST_APP_PATH.'core/data/'.$file)) {
                unlink(TEST_APP_PATH.'core/data/'.$file);
            }
        }
    }

    public function tearDown()
    {
        // @todo delete _validated.xml if exists
        // @todo delete _processed.xml if exists
    }

}