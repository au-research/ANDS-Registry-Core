<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Cache\Cache;
use ANDS\Registry\Providers\OAIRecordRepository;
use MinhD\OAIPMH\Exception\BadArgumentException;
use MinhD\OAIPMH\ServiceProvider;

class OaiHandler extends Handler
{
    /** @var int cache ttl in minutes */
    protected static $cacheDuration = 60;

    public function handle()
    {
        $this->getParentAPI()->providesOwnResponse();
        $options = $_GET;
        return Cache::file()->remember('oai.' . md5(json_encode($options)), static::$cacheDuration,
            function () use ($options) {
                return $this->handleOAIRequest($options);
            });
    }

    /**
     * Handle the OAI Request
     * Using MinhD\OAIPMH\ServiceProvider
     *
     * @param $options
     * @return string
     */
    private function handleOAIRequest($options)
    {
        $provider = new ServiceProvider(
            new OAIRecordRepository()
        );
        $provider->setOptions($options);
        try {
            $response = $provider->get()->getResponse();
        } catch (\Exception $e) {
            $exception = new BadArgumentException(get_exception_msg($e));
            $response = $provider->getExceptionResponse($exception);
            return (string)$response->getResponse()->getBody();
        }
        return (string)$response->getBody();
    }
}