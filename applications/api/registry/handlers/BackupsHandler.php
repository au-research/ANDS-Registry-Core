<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Registry\API\Router;

class BackupsHandler extends Handler
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

        // GET /api/registry/backups
        $router->get('backups', 'BackupsAPI@index');

        // GET /api/registry/backups/{backupId}
        $router->get('backups/(\w+)', 'BackupsAPI@show');

        // POST /api/registry/backups/
        $router->post('backups', 'BackupsAPI@store');

        // [PUT|POST] /api/registry/backups/{backupId}/_restore
        $router->route(['PUT', 'POST'], 'backups/(\w+)/_restore', 'BackupsAPI@restore');

        $router->route(['PUT', 'POST'], 'backups/(\w+)/_validate', 'BackupsAPI@validate');

        return $this->format($router->execute());
    }

    public function format($data) {
        return json_encode($data);
    }
}