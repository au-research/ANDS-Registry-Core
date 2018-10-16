<?php

namespace ANDS\API\Registry\Handler;

use ANDS\Cache\Cache;
use ANDS\Registry\Providers\OAIRecordRepository;
use ANDS\OAI\Exception\BadArgumentException;
use ANDS\OAI\ServiceProvider;

class OaiHandler extends Handler
{
    /** @var int cache ttl in minutes */
    protected static $cacheDuration = 60;

    public function handle()
    {
        $this->getParentAPI()->providesOwnResponse();
        $options = array_merge($_GET, $_POST);
        return $this->handleOAIRequest($options);
    }

    /**
     * Handle the OAI Request
     * Using ANDS\OAI\ServiceProvider
     *
     * @param $options
     * @return string
     */
    public function handleOAIRequest($options)
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