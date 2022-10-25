<?php

namespace ANDS\Task;

use PHPUnit\Framework\TestCase;

class TaskModelTest extends TestCase
{
    /** @test */
    public function it_creates_task()
    {
        // when a new taskmodel is created & saved
        $task = new TaskModel();
        $task->save();

        // it has an automatically generated id
        $this->assertNotNull($task->id);

        // and when deleted, it's gone & cleaned up
        $task->delete();
        $this->assertNull($task->fresh());
    }

    /** @test */
    public function it_casts_data_to_array_properly()
    {
        // when a task model is saved with task data
        $task = new TaskModel();
        $task->data = [
            'key' => 'some value',
            'nested_key' => [
                'nested_key_lv2' => 'some nested value'
            ]
        ];
        $task->save();

        // get a fresh instance
        $task = $task->fresh();

        // task->data is an array when re-obtained
        $this->assertTrue(is_array($task->data));
        $this->assertEquals("some value", $task->data['key']);
        $this->assertEquals("some nested value", $task->data['nested_key']['nested_key_lv2']);

        // clean up
        $task->delete();
    }


}
