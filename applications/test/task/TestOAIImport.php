<?php


namespace ANDS\Test;

use ANDS\API\Task\ImportTask;
use ANDS\API\Task\TaskManager;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository as Repo;

class TestOAIImport extends UnitTest
{
    /** @test **/
    public function test_it_should_import_oai_records_correctly()
    {
        $taskManager = new TaskManager($this->ci->db, $this->ci);
        $task = $taskManager->addTask([
            'name' => 'Automated test test_it_should_import_oai_records_correctly',
            'type' => 'POKE',
            'frequency' => 'ONCE',
            'priority' => 1,
            'params'=>'class=import&ds_id=209&batch_id=OAI'
        ]);

        $taskObject = $taskManager->getTaskObject($task);

        $importTask = new ImportTask();
        $defaultImportTasks = $importTask->getDefaultImportSubtasks();
        foreach ($defaultImportTasks as $t) {
            $taskObject->run();
        }

        $record = Repo::getPublishedByKey("OAI-Test-Collection-1");
        $this->assertInstanceOf($record, RegistryObject::class);

        $record = Repo::getPublishedByKey("OAI-Test-Collection-2");
        $this->assertInstanceOf($record, RegistryObject::class);

        $record = Repo::getPublishedByKey('OAI-Test-Collection-3');
        $this->assertInstanceOf($record, RegistryObject::class);

        $record = Repo::getPublishedByKey('OAI-Test-Collection-4');
        $this->assertInstanceOf($record, RegistryObject::class);

        $record = Repo::getPublishedByKey('OAI-Test-Collection-5');
        $this->assertInstanceOf($record, RegistryObject::class);

        $record = Repo::getPublishedByKey('OAI-Test-Party-1');
        $this->assertInstanceOf($record, RegistryObject::class);

        // TODO: should delete: OAI-TO-BE-DELETED, OAI-TO-BE-DELETED-2

        // shouldn't exist INVALID-MISSING-GROUP
        $record = Repo::getPublishedByKey('INVALID-MISSING-GROUP');
        $this->assertNull($record);
        // shouldn't exist INVALID-correction
        $record = Repo::getPublishedByKey('INVALID-correction');
        $this->assertNull($record);
        // shouldn't exist: INVALID-MISSING-TYPE
        $record = Repo::getPublishedByKey('INVALID-MISSING-TYPE');
        $this->assertNull($record);
    }

    public function setUp()
    {
        $importTask = new ImportTask();
        $importTask->bootEloquentModels();
        Repo::completelyEraseRecord("OAI-Test-Collection-1");
        Repo::completelyEraseRecord("OAI-Test-Collection-2");
        Repo::completelyEraseRecord("OAI-Test-Collection-3");
        Repo::completelyEraseRecord("OAI-Test-Collection-4");
        Repo::completelyEraseRecord("OAI-Test-Collection-5");
        Repo::completelyEraseRecord("OAI-Test-Party-1");
        Repo::completelyEraseRecord("INVALID-MISSING-GROUP");
        Repo::completelyEraseRecord("INVALID-correction");
        Repo::completelyEraseRecord("INVALID-MISSING-TYPE");
    }
}