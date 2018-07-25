<?php
namespace ANDS\API\Registry\Handler;

use ANDS\Registry\Providers\OAIRecordRepository;
use MinhD\OAIPMH\ServiceProvider;

class OaiHandler extends Handler
{
    public function handle()
    {


        $this->getParentAPI()->providesOwnResponse();

        $options = $_GET;

        $provider = new ServiceProvider(
            new OAIRecordRepository()
        );

        $provider->setOptions($options);
        try {
            $response = $provider->get()->getResponse();
        } catch (\Exception $e) {
            dd(get_exception_msg($e));
        }


        return (string) $response->getBody();
    }
}