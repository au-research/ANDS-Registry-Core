<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Registry\API\Router;

class TasksHandler extends Handler
{
    public function handle()
    {
        $this->getParentAPI()->providesOwnResponse();
        $this->getParentAPI()->outputFormat = "application/json";

        $router = new Router('/api/registry/');
        $router->resource('tasks', 'TasksAPI');
        $router->get('tasks/(\w+)/subtasks', 'TasksAPI@showSubtasks');
        $router->get('tasks/(\w+)/subtasks/(\w+)', 'TasksAPI@showSubtask');
        $router->get('tasks/(\w+)/log', 'TasksAPI@showLog');
        $router->get('tasks/(\w+)/resume', 'TasksAPI@resume');

        return $this->format($router->execute());
    }

    public function format($data)
    {
        return json_encode($data);
    }
}