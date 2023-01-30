<?php

namespace ANDS\Task;

use ANDS\API\Task\ImportTask;
use ANDS\API\Task\Task;
use ANDS\File\Storage;
use PHPUnit\Framework\TestCase;

class TaskRepositoryTest extends TestCase
{

    public function testGetById()
    {
        // when a new task model is created & saved
        $model = new TaskModel();
        $model->fill([
            'name' => 'test task',
            'params' => http_build_query([
                'class' => 'import'
            ])
        ]);
        $model->save();

        // it has an automatically generated id
        $this->assertNotNull($model->id);

        $task = TaskRepository::getById($model->id);

        $this->assertNotNull($task);
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals("test task", $task->getName());
        $this->assertEquals("class=import", $task->getParams());
        $this->assertEquals(Task::$STATUS_PENDING, $task->getStatus());

        // clean up
        $model->delete();
    }

    public function testGetById_null()
    {
        $task = TaskRepository::getById(0);
        $this->assertNull($task);
    }

    public function testGetTaskObject()
    {
        $task = TaskRepository::getTaskObject([
            'params' => 'class=import'
        ]);
        $this->assertNotNull($task);
        $this->assertInstanceOf(ImportTask::class, $task);
    }

    public function testSaveTaskObject()
    {
        // when a new task model is created & saved to the database
        $model = new TaskModel();
        $model->fill([
            'name' => 'test task',
            'params' => http_build_query([
                'class' => 'import'
            ])
        ]);
        $model->save();

        // and updating the task status and saving it
        $task = TaskRepository::getById($model->id);
        $task->setStatus(Task::$STATUS_COMPLETED);
        $task->save();

        // it is saved to the database via model
        $model = $model->fresh();
        $this->assertEquals(Task::$STATUS_COMPLETED, $model->status);

        // clean up
        $model->delete();
    }

    public function testLoadTaskObjectBroken()
    {
        date_default_timezone_set("Australia/Melbourne");
        $model = new TaskModel();
        $model->fill([
            'name' => 'test task',
            'data' => Storage::disk('test')->get('task/arc_saved_task_data.txt'),
            'params' => http_build_query([
                'class' => 'import'
            ])
        ]);
        $task = TaskRepository::getTaskObject($model->toArray());
        $this->assertEquals("Task stopped with error: TaskData couldn't be imported json_last_error_msg: Syntax error", $task->getMessage());
    }
}
