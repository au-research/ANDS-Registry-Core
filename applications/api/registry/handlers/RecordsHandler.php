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

        $router->get('records/(\w+)/orcid', 'RecordsMiscController@orcid');
        $router->get('records/(\w+)/orcid/validate', 'RecordsMiscController@orcidValidate');

        $router->get('records/(\w+)/scholix', 'RecordsMiscController@scholix');

        $router->get('records/(\w+)/rifcs', 'RecordsMiscController@rifcs');
        $router->get('records/(\w+)/dci', 'RecordsMiscController@dci');
        $router->get('records/(\w+)/dci/validate', 'RecordsMiscController@dciValidate');
        $router->get('records/(\w+)/oai_dc', 'RecordsMiscController@oai_dc');
        $router->get('records/(\w+)/json_ld', 'RecordsMiscController@json_ld');
        $router->get('records/(\w+)/mycelium', 'RecordsMiscController@mycelium');

        $router->get('records/(\w+)/versions', 'RecordsVersionsController@index');
        $router->get('records/(\w+)/versions/(\w+)', 'RecordsVersionsController@show');

        $router->get('records/(\w+)/graph', 'RecordsGraphController@index');

        $router->route(['GET', 'PUT', 'POST'], 'records/(\w+)/sync', 'RecordsController@sync');
//        dd($router->getMatch());
        return $this->format($router->execute());
    }

    public function format($data)
    {
        return json_encode($data);
    }
}