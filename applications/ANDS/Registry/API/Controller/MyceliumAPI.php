<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Util\Config;
use Exception;
use SimpleXMLElement;

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

    public function processIdentifier($method, $query)
    {
        // currently only support 'normalise'
        // it retrieves the normalised version of any given identifier using the rules defined in Mycelium
        if ($method == 'normalise') {
            parse_str($query, $params);
            $identifier_type = 'uri';
            if(isset($params['identifier_type'])){
                $identifier_type = $params['identifier_type'];
            }
            $result = $this->myceliumClient->normaliseIdentifier($params['identifier_value'], $identifier_type);
            if ($result->getStatusCode() == 200) {
                $content =  json_decode($result->getBody()->getContents(), true);
                $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><identifier></identifier>');
                static::arraytoXML($content, $xml);
                return $xml->asXML();
            } else {
                throw new Exception("Error retrieving normalised Identifier for value:{} type:{}",
                    $params['identifier_value'], $params['identifier_type']);
            }
        }
    }

    private static function arraytoXML($json_arr, &$xml)
    {
        foreach($json_arr as $key => $value)
        {
            if(is_int($key))
            {
                $key = 'Element'.$key;  //To avoid numeric tags like <0></0>
            }
            if(is_array($value))
            {
                $label = $xml->addChild($key);
                static::arrayToXml($value, $label);  //Adds nested elements.
            }
            else
            {
                $xml->addChild($key, $value);
            }
        }
    }


}