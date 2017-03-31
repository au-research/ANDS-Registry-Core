<?php
namespace ANDS\API\Registry\Handler;

use ANDS\Registry\Providers\OAIRecordRepository;
use MinhD\OAIPMH\ServiceProvider;

class OaiHandler extends Handler
{
    public function handle()
    {
        $provider = new ServiceProvider(
            new OAIRecordRepository()
        );

        $this->getParentAPI()->providesOwnResponse();

        $options = $_GET;
        $provider->setOptions($options);
        try {
            $response = $provider->get()->getResponse();
        } catch (\Exception $e) {
            dd($e->getTraceAsString());
        }


        return (string) $response->getBody();
    }
}