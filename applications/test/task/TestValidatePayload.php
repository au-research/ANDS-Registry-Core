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
        $task = $this->getImportTask();
        $this->assertEquals("ValidatePayload", $task->name);
        $this->assertEquals("PENDING", $task->status);
    }

    /** @test **/
    public function test_it_should_load_payload_to_parent_task()
    {
        $task = $this->getImportTask();
        $payload = $task->parent()->getPayloads();
        $this->assertTrue(is_array($payload));
        $this->assertTrue(count($payload) > 0);
        $this->assertTrue(count(array_first($payload)) > 0);
    }

    /** @test **/
    public function test_it_should_validate_rifcs_xml()
    {
        $task = $this->getImportTask();
        $task->run();
        //@todo check validated file generated
        //@todo check parent payload updated to be validated form
    }

    /** @test */
    public function test_it_should_validate_rifcs_xml_but_remove_invalidated_ones()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecords'
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
    }

    /** @test **/
    public function test_it_should_return_when_no_payload_provided()
    {
        $task = $this->getImportTask();
        $task->parent()->setBatchID("asdfasdfafds")->loadPayload();
        $task->run();
        $this->assertGreaterThanOrEqual(count($task->parent()->getError()), 1);
    }

    /** @test **/
    public function test_it_should_handle_invalid_doc_correctly()
    {
        $this->ci->config->set_item('harvested_contents_path', TEST_APP_PATH . 'core/data/');
        $dataSource = DataSourceRepository::getByKey("AUTEST1");
        $importTask = new ImportTask;
        $importTask->init([
            "name" => "This import task should fail gracefully",
            "params" => "ds_id=$dataSource->data_source_id&batch_id=Invalid_XML_Document"
        ]);

        $importTask
            ->skipLoadingPayload()
            ->setPayload("Invalid_XML_Document", new Payload(TEST_APP_PATH . 'core/data/Invalid_XML_Document.xml'));

        $importTask->initialiseTask()->enableRunAllSubTask();

        $importTask->run();
        $this->assertEquals("STOPPED", $importTask->getStatus());
        $this->assertContains("XML does not pass validation", $importTask->getError());
        $this->assertTrue($importTask->hasError());
    }

    /** @test **/
    public function test_it_should_handle_no_document_correctly()
    {
        $this->ci->config->set_item('harvested_contents_path', TEST_APP_PATH . 'core/data/');
        $dataSource = DataSourceRepository::getByKey("AUTEST1");
        $importTask = new ImportTask;
        $importTask->init([
            "name" => "This import task should fail gracefully",
            "params" => "ds_id=$dataSource->data_source_id&batch_id=Invalid_XML_Document"
        ]);

        $importTask
            ->skipLoadingPayload()
            ->setPayload("No_Document", new Payload(TEST_APP_PATH. 'core/data/No_Document.xml'));

        $importTask->initialiseTask()->enableRunAllSubTask();

        $importTask->run();

        $this->assertFalse($importTask->hasError());
    }

    /**
     * Helper
     * Return an ImportSubTask for ValidatePayload for use each test
     *
     * @return ImportSubTask
     */
    private function getImportTask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=593EB384AFFE59EAEB2CADE99E39454361C1C0AC'
        ])->initialiseTask();

        $task = $importTask->getTaskByName("ValidatePayload");
        return $task;
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