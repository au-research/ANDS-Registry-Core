<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Registry\API\Router;

class OrcidsHandler extends Handler {

    public function handle()
    {
        $this->getParentAPI()->providesOwnResponse();
        $this->getParentAPI()->outputFormat = "application/json";

        $router = new Router('/api/registry/');

        $router->get('orcids/(.*)/suggested', 'ORCIDController@suggestedDatasets');
        $router->get('orcids/(.*)/exports', 'ORCIDController@exports');
        $router->get('orcids/(.*)/works', 'ORCIDController@works');
        $router->route(['put', 'post'], 'orcids/(.*)/works', 'ORCIDController@import');
        $router->get('orcids/(.*)', 'ORCIDController@show');

        //        dd($router->getMatch());
        return $this->format($router->execute());
    }

    public function format($data)
    {
        return json_encode($data);
    }
}