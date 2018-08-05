<?php
namespace ANDS\API\Registry\Handler;

use ANDS\Registry\Providers\OAIRecordRepository;
use MinhD\OAIPMH\Exception\BadArgumentException;
use MinhD\OAIPMH\OAIException;
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
            $exception = new BadArgumentException(get_exception_msg($e));
            $response = $provider->getExceptionResponse($exception);
            return (string) $response->getResponse()->getBody();
        }

        return (string) $response->getBody();
    }
}