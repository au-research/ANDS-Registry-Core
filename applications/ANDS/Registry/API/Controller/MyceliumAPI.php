<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Util\Config;
use Exception;

class MyceliumAPI extends HTTPController
{

    private $myceliumClient;

    public function __construct()
    {
        $myceliumUrl = Config::get('mycelium.url');
        $this->myceliumClient = new MyceliumServiceClient($myceliumUrl);
    }

    /**
     * @param $uuid
     * @return false|string
     * @throws Exception
     */
    public function showRequestById($uuid)
    {
        $result = $this->myceliumClient->getRequestById($uuid);
        if ($result->getStatusCode() == 200) {
            $request = json_decode($result->getBody()->getContents(), true);
            return json_encode($request);
        } else {
            throw new Exception("Error displaying RequestID: $uuid");
        }
    }

    /**
     * @param $uuid
     * @return string
     * @throws Exception
     */
    public function showRequestLogById($uuid)
    {
        $result = $this->myceliumClient->getRequestLogById($uuid);
        if ($result->getStatusCode() == 200) {
            return $result->getBody()->getContents();
        } else {
            throw new Exception("Error displaying log for RequestID: $uuid");
        }
    }

    /**
     * @param $uuid
     * @return false|string
     * @throws Exception
     */
    public function showRequestQueueById($uuid) {
        $result = $this->myceliumClient->getRequestQueueById($uuid);
        if ($result->getStatusCode() == 200) {
            $request = json_decode($result->getBody()->getContents(), true);
            return json_encode($request);
        } else {
            throw new Exception("Error displaying queue for RequestID: $uuid");
        }
    }
}