<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Registry\API\Router;

class RecordsHandler extends Handler {

    public function handle()
    {
        $router = new Router('/api/registry/');
        $router->resource('records', 'RecordsController');
        $router->get('records/(\w+)/relationships', 'RecordsRelationshipController@index');
        $router->get('records/(\w+)/links', 'RecordsLinksController@index');
        return $router->execute();
    }
}