<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Task\TaskRepository;

class TasksAPI extends HTTPController implements RestfulController
{

    public function index()
    {
        throw new \Exception("Not Implemented");
    }

    public function show($id)
    {
        $task = TaskRepository::getById($id);
        if (!$task) {
            throw new \Exception("Task id:$id doesn't exist");
        }
        return $task->toArray();
    }

    public function update()
    {
        throw new \Exception("Not Implemented");
    }

    public function destroy()
    {
        throw new \Exception("Not Implemented");
    }

    public function add()
    {
        throw new \Exception("Not Implemented");
    }

    public function showSubtasks($id)
    {
        $task = TaskRepository::getById($id);
        if (!$task) {
            throw new \Exception("Task id:$id doesn't exist");
        }
        $task->loadSubTasks();
        return $task->getSubtasks();
    }

    public function showSubtask($id, $name)
    {
        $task = TaskRepository::getById($id);
        if (!$task) {
            throw new \Exception("Task id:$id doesn't exist");
        }
        $task->loadSubTasks();
        return $task->getTaskByName($name);
    }

    public function showLog($id)
    {
        $task = TaskRepository::getById($id);
        if (!$task) {
            throw new \Exception("Task id:$id doesn't exist");
        }
        $logPath = $task->getLogPath();

        if (! is_file($logPath) || ! is_readable($logPath)) {
            throw new \Exception("Log file for Task[id=$id] is not accessible");
        }

        $this->printText(@file_get_contents($logPath));
    }

    private function printText($text)
    {
        header('Content-type: application/text');
        echo $text;
        die();
    }
}