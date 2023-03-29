<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Registry\API\Router;
use Exception;

class MyceliumServicesHandler extends Handler
{
    /**
     * handles /api/registry/mycelium
     * @return false|string
     * @throws Exception
     */
    public function handle()
    {
        header("Access-Control-Allow-Origin: *");
        $this->getParentAPI()->providesOwnResponse();
        $this->getParentAPI()->outputFormat = "application/xml";

        $router = new Router('/api/registry/');
        //api/registry/myceliumservices/identifiers/normalise?identifier_value=https://doi.org/10.13039/501100000925&identifier_type=Cross%20ref%20ID
        $router->get('myceliumservices/identifiers/(.*)', 'MyceliumAPI@processIdentifier');
        return $router->execute();
    }
}