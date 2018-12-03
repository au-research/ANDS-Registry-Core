<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 10/8/18
 * Time: 3:31 PM
 */

namespace ANDS\Registry\Providers\ServiceDiscovery;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use ANDS\Util\XMLUtil;

class ServiceProducer {
   
    private $errors = array();
    public $responseCode;
    private $http;
    private  $response;
    
    public function __construct($service_url)
    {
        $this->http = new GuzzleClient($service_url);
    }
    
    function processServices($service_json_file){
        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/xml',
        ];
        $response = "";
        $request = $this->http->post('processServices', $headers, $service_json_file);

        try {
            $response = $request->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors = $e->getResponse()->json();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getResponse()->json();
            $this->responseCode = $e->getCode();
        }
        catch (\Exception $e) {
            $this->errors[] = $e->getResponse()->json();
            $this->responseCode = $e->getCode();
        }
        $this->response = $response->xml()->asXML();
    }

    public function getServicebyURL($url, $type)
    {
        $response = "";
        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/xml',
        ];
        try {
            $response = $this->http->get('getRifService', $headers, ["query" => ['url'=>$url, 'type' => $type]])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->response = $response->xml()->asXML();
    }
    
    function getServiceCount(){
        $registryObjects = XMLUtil::getElementsByName($this->response, 'registryObject');
        return count($registryObjects);
    }

    function getRegistryObjects(){
        return $this->response;
    }
    
}