<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Registry\API\Router;

class QueuesHandler extends Handler
{
    /**
     * handles /api/registry/backups
     *
     * @return false|string
     * @throws \Exception
     */
    public function handle()
    {
        $this->getParentAPI()->providesOwnResponse();
        $this->getParentAPI()->outputFormat = "application/json";

        $router = new Router('/api/registry/');

        // GET /api/registry/queues
        $router->get('queues', 'QueuesAPI@index');

        // GET /api/registry/queues/{queueId}
        $router->get('queues/(.*)/jobs', 'QueuesAPI@jobs');
        $router->get('queues/(.*)', 'QueuesAPI@show');

        return $this->format($router->execute());
    }

    public function format($data) {
        return json_encode($data);
    }
}