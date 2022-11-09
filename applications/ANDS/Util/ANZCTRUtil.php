<?php

namespace ANDS\Util;
use Exception;
use GuzzleHttp\Client;


class ANZCTRUtil
{
    public static function retrieveMetadata($identifier){
        $metadata = '';

        $soapUrl = "https://anzctr.org.au";
        $soapHeader = '<?xml version="1.0" encoding="utf-8"?>'
                    .'<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">'
                    .'<soap12:Body>'
                    .'<AnzctrTrialDetails xmlns="http://anzctr.org.au/WebServices/AnzctrWebServices">'
                    .'<ids>';

        $soapFooter = '</ids></AnzctrTrialDetails></soap12:Body></soap12:Envelope>';
        $client = new Client(['base_url'=>$soapUrl]);
        try {
            $response = $client->post(
                'https://anzctr.org.au/WebServices/AnzctrWebservices.asmx',
                [
                    'body'    => $soapHeader.$identifier.$soapFooter,
                    'headers' => ['User-Agent' => 'ARDC Harvester',
                        'Accept'=>'*/*',
                        'Accept-Encoding'=>'gzip, deflate, br',
                        'Connection'=>'keep-alive',
                        'Content-Type' => 'text/xml; charset=utf-8',
                        'SOAPAction' => 'http://anzctr.org.au/WebServices/AnzctrWebServices/AnzctrTrialDetails' // SOAP Method to post to
                    ]
                ]
            );

            if ($response->getStatusCode() === 200) {
                // Success!
                $xmlString = (string) $response->getBody();
                // by default we use ro: as a namesapce prefix for everything
                $result = XMLUtil::getElementsByXPath($xmlString, "//ro:AnzctrTrialDetailsResult", 'http://anzctr.org.au/WebServices/AnzctrWebServices');
                $metadata = (string) $result[0];
            } else {
                throw new Exception("Unable to retrieve anzctr metadata status code:" . $response->getStatusCode());
            }

        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $metadata;
    }




}