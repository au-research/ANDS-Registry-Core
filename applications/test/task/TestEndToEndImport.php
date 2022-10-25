<?php


namespace ANDS\Test;

use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;


/**
 * Class TestEndToEndImport
 * @package ANDS\Test
 */
class TestEndToEndImport extends UnitTest
{
    /** @test **/
    public function test_it_should_import_a_record_step_by_step()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->initialiseTask();

        // PopulateImportOptions
        $importTask->run_task();

        $taskArray = $importTask->toArray();
        $this->assertEquals(
            "PUBLISHED", $taskArray["data"]["dataSourceDefaultStatus"]
        );

        // ValidatePayload
        $importTask->run_task();

        $this->assertFalse(
            $importTask->getTaskByName("ValidatePayload")->hasError()
        );
        $this->assertTrue($importTask->hasPayload());

        // ProcessPayload
        $importTask->run_task();

        $this->assertFalse(
            $importTask->getTaskByName("ProcessPayload")->hasError()
        );
         $this->assertTrue($importTask->hasPayload());

        // Ingest
        $importTask->run_task();

        $importedRecords = $importTask->getTaskData('importedRecords');
        $this->assertTrue(count($importedRecords) > 0);

        $record = RegistryObject::where('key', 'minh-test-record-pipeline')->first();
        $this->assertTrue($record);


        $this->assertNull($importTask->getTaskData('deletedRecords'));

        // ProcessDelete
        $importTask->run_task();

        // ProcessCoreMetadata
        $importTask->run_task();

        unset($record);
        $record = RegistryObject::where('key', 'minh-test-record-pipeline')->first();

        $this->assertEquals($record->title, "Minh test record pipeline");
        $this->assertEquals($record->type, "collection");
        $this->assertEquals($record->status, "PUBLISHED");
        $this->assertEquals($record->slug, "minh-test-pipeline");
        $this->assertEquals($record->record_owner, "SYSTEM");
        $this->assertEquals($record->group, "AUTestingRecords");
        $this->assertEquals($record->getRegistryObjectAttributeValue('harvest_id'), "AUTestingRecordsImport");

        // ProcessIdentifiers
        $importTask->run_task();

        // ProcessQualityMetadata
        $importTask->run_task();

        // check that level_html is generated and quality_level attribute is there
        $this->assertEquals(0, $record->getRegistryObjectAttributeValue('warning_count'));
        $this->assertEquals(0, $record->getRegistryObjectAttributeValue('error_count'));
        $this->assertEquals(3, $record->getRegistryObjectAttributeValue('quality_level'));

        $this->assertTrue($record->getRegistryobjectMetadata("level_html"));
        $this->assertTrue($record->getRegistryobjectMetadata("quality_html"));

        // indexPortal
        $importTask->run_task();

        // check that metadata solr_doc is generated
        $this->assertTrue($record->getRegistryobjectMetadata("solr_doc"));

    }

    /** @test **/
    public function test_it_should_import_a_record_all_at_once()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->initialiseTask();
        $importTask->enableRunAllSubTask()->run();

        $record = RegistryObject::where('key', 'minh-test-record-pipeline')->first();


        $this->assertEquals(0, $record->getRegistryObjectAttributeValue('warning_count'));
        $this->assertEquals(0, $record->getRegistryObjectAttributeValue('error_count'));
        $this->assertEquals(3, $record->getRegistryObjectAttributeValue('quality_level'));

        $this->assertTrue($record->getRegistryobjectMetadata("level_html"));
        $this->assertTrue($record->getRegistryobjectMetadata("quality_html"));

        $this->assertTrue($record->getRegistryobjectMetadata("solr_doc"));
    }

    /** @test **/
    public function test_it_should_import_a_record_into_draft_when_required()
    {
        // import the record in as draft
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport&targetStatus=DRAFT'
        ])->initialiseTask();
        $importTask->enableRunAllSubTask()->run();

        // make sure it's draft
        $record = RegistryObjectsRepository::getByKeyAndStatus("minh-test-record-pipeline", "DRAFT");
        $this->assertEquals("DRAFT", $record->status);
    }

    public function setUp()
    {
        $importTask = new ImportTask();
        $importTask->bootEloquentModels();
    }

    public function tearDown()
    {
        RegistryObjectsRepository::completelyEraseRecord('minh-test-record-pipeline');
    }
}