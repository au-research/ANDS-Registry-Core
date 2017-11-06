<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Registry\API\Router;

class RecordsHandler extends Handler {

    public function handle()
    {
        $this->getParentAPI()->providesOwnResponse();
        $this->getParentAPI()->outputFormat = "application/json";

        $router = new Router('/api/registry/');
        $router->get('records/resolve', 'RecordsController@resolve');
        $router->resource('records', 'RecordsController');
        $router->get('records/(\w+)/relationships', 'RecordsRelationshipController@index');
        $router->get('records/(\w+)/links', 'RecordsLinksController@index');
        $router->get('records/(\w+)/identifiers', 'RecordsIdentifiersController@index');

        $router->route(['GET', 'PUT', 'POST'], 'records/(\w+)/sync', 'RecordsController@sync');
//        dd($router->getMatch());
        return $this->format($router->execute());
    }

    public function format($data)
    {
        return json_encode($data);
    }
}