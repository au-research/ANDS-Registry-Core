<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Registry\API\Router;

class WebhookHandler extends Handler
{
    /**
     * handles /api/registry/webhook
     * @return false|string
     * @throws Exception
     */
    public function handle()
    {
        $this->getParentAPI()->providesOwnResponse();
        $this->getParentAPI()->outputFormat = "application/json";

        $router = new Router('/api/registry/');

        // POST /api/registry/webhook
        $router->post('webhook', 'WebhookAPI@accept');

        return $router->execute();
    }
}