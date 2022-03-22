<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Mycelium\RelationshipSearchService;
use ANDS\Registry\API\Router;

class RelationshipsHandler extends Handler
{
    /**
     * handles /api/registry/relationships
     * @return false|string
     * @throws \Exception
     */
    public function handle()
    {
        $this->getParentAPI()->providesOwnResponse();
        $this->getParentAPI()->outputFormat = "application/json";

        $router = new Router('/api/registry/');
        // GET /api/registry/relationships
        $router->get('relationships', 'RelationshipsAPI@index');

        return $router->execute();
    }
}