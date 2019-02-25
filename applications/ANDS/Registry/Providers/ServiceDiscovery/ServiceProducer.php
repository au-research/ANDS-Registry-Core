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
        $this->http->setDefaultOption('verify', false);
    }
    
    function processServices($service_json_file){
        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
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
            return;
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getResponse()->json();
            $this->responseCode = $e->getCode();
            return;
        }
        catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
            return;
        }
        $this->response = $response->json();
    }

    function getSummary(){
        return $this->response['summary'];
    }

    function getRegistryObjects(){
        return base64_decode($this->response['B64XML']);
    }

    function mockResponse($jsonString){
        $this->response = json_decode($jsonString, true);
    }
    
}