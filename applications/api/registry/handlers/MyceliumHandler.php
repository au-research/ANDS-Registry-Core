<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Registry\API\Router;
use Exception;

class MyceliumHandler extends Handler
{
    /**
     * handles /api/registry/mycelium
     * @return false|string
     * @throws Exception
     */
    public function handle()
    {
        $this->getParentAPI()->providesOwnResponse();
        $this->getParentAPI()->outputFormat = "application/json";

        $router = new Router('/api/registry/');

        // GET /api/registry/mycelium/requests/{uuid}/[logs|queue]
        $router->get('mycelium/requests/(.*)/queue', 'MyceliumAPI@showRequestQueueById');
        $router->get('mycelium/requests/(.*)/logs', 'MyceliumAPI@showRequestLogById');
        $router->get('mycelium/requests/(.*)', 'MyceliumAPI@showRequestById');
        return $router->execute();
    }
}