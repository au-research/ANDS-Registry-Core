<?php

namespace ANDS\API\Task;

use ANDS\Task\TaskModel;
use ANDS\Task\TaskRepository;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testATaskCanBeInit()
    {
        $task = new Task();
        $task->init([
            'name' => 'test task',
            'data' => [
                'some key' => 'some value'
            ]
        ]);
        $this->assertEquals("test task", $task->getName());
        $this->assertEquals(Task::$STATUS_PENDING, $task->getStatus());
        $this->assertTrue(is_array($task->getTaskData()));
    }

    public function testTaskCanBeRun()
    {
        $task = new Task();
        $task->run();
        $this->assertArrayHasKey('benchmark', $task->getTaskData());
        $this->assertNotNull($task->getLastRun());
        $this->assertEquals(Task::$STATUS_COMPLETED, $task->getStatus());
    }

}
