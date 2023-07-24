<?php

use ANDS\OAI\Exception\BadArgumentException;
use ANDS\OAI\ServiceProvider;
use ANDS\Registry\Providers\OAIRecordRepository;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Oai extends MX_Controller
{
    /**
     * nothing special; initialise the session and that's about it
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * OAI doesn't really support nicely routed URLs, so everything is going
     * to happen via this toplevel route, and get farmed off accordingly
     * handles /registry/services/oai endpoint
     * @throws \ANDS\OAI\Exception\OAIException
     */
    public function index()
    {
        header('Content-Type: application/xml');
        date_default_timezone_set(\ANDS\Util\Config::get('app.timezone'));

        $options = array_merge($_GET, $_POST);

        $provider = new ServiceProvider(new OAIRecordRepository());
        $provider->setOptions($options);

        try {
            $response = $provider->get();
        } catch (\Exception $e) {
            $exception = new BadArgumentException(get_exception_msg($e));
            $response = $provider->getExceptionResponse($exception);
            echo (string)$response->getResponse()->getBody();
            return;
        }

        monolog([
            'event' => $response->errored() ? 'error' : $options['verb'],
            'errors' => implode(' ', $response->getErrors()),
            'errored' => $response->errored(),
            'request' => $options
        ], "oai_api", "info", true);

        echo (string)$response->getResponse()->getBody();
        return;
    }
}
