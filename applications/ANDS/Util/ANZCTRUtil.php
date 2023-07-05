<?php

namespace ANDS\Util;
use Exception;
use GuzzleHttp\Client;


class ANZCTRUtil
{
    /**
     * @param $identifier string the ANZCTR Identifier
     * @return string the Trial metadata V1 as XML
     * @throws Exception
     */
    public static function retrieveMetadata($identifier){
        $metadata = '';
        // all ACTRN identifiers must be 14 digit (without the ACTRN prefix)
        $identifier = substr($identifier, -14);
        if(strlen($identifier) !== 14){
            throw new Exception("ACTRN number must be 14 digit: " . $identifier);
        }
        if(!is_numeric($identifier)){
            throw new Exception("the 14 digit ACTRN ID must contain only numbers: " . $identifier);
        }
        // all ACTRN identifiers must be prefixed with 'ACTRN' when using the SOAP API
        $identifier = "ACTRN" . $identifier;

        $soapUrl = "https://www.anzctr.org.au";
        $soapHeader = '<?xml version="1.0" encoding="utf-8"?>'
                    .'<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">'
                    .'<soap12:Body>'
                    .'<AnzctrTrialDetails xmlns="http://anzctr.org.au/WebServices/AnzctrWebServices">'
                    .'<ids>';

        $soapFooter = '</ids></AnzctrTrialDetails></soap12:Body></soap12:Envelope>';
        $client = new Client(['base_url'=>$soapUrl]);
        try {
            $response = $client->post(
                'https://www.anzctr.org.au/WebServices/AnzctrWebservices.asmx',
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
                // by default, we use ro: as a namesapce prefix for everything
                $result = XMLUtil::getElementsByXPath($xmlString, "//ro:AnzctrTrialDetailsResult", 'http://anzctr.org.au/WebServices/AnzctrWebServices');
                ANZCTRUtil::validateContent((string) $result[0], $identifier);
                $metadata = (string) $result[0];
            } else {
                throw new Exception("Unable to retrieve anzctr metadata status code:" . $response->getStatusCode());
            }

        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $metadata;
    }


    /** Retreive Version 2 metadata
     * @param $identifier string ANZCTR Identifier
     * @return string the Trial metadata V2 as XML
     * @throws Exception
     */
    public static function retrieveMetadataV2($identifier){
        $metadata = '';
        // all ACTRN identifiers must be 14 digit (without the ACTRN prefix)
        $identifier = substr($identifier, -14);
        if(strlen($identifier) !== 14){
            throw new Exception("ACTRN number must be 14 digit: " . $identifier);
        }
        if(!is_numeric($identifier)){
            throw new Exception("the 14 digit ACTRN ID must contain only numbers: " . $identifier);
        }
        // all ACTRN identifiers must be prefixed with 'ACTRN' when using the SOAP API
        $identifier = "ACTRN" . $identifier;

        $soapUrl = "https://www.anzctr.org.au";
        $soapHeader = '<?xml version="1.0" encoding="utf-8"?>'
            .'<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">'
            .'<soap12:Body>'
            .'<AnzctrTrialDetailsV2 xmlns="http://anzctr.org.au/WebServices/AnzctrWebServices">'
            .'<ids>';

        $soapFooter = '</ids></AnzctrTrialDetailsV2><version>2</version></soap12:Body></soap12:Envelope>';
        $client = new Client(['base_url'=>$soapUrl]);
        try {
            $response = $client->post(
                'https://www.anzctr.org.au/WebServices/AnzctrWebservices.asmx?op=AnzctrTrialDetailsV2',
                [
                    'body'    => $soapHeader.$identifier.$soapFooter,
                    'headers' => ['User-Agent' => 'ARDC Harvester',
                        'Accept'=>'*/*',
                        'Accept-Encoding'=>'gzip, deflate, br',
                        'Connection'=>'keep-alive',
                        'Content-Type' => 'text/xml; charset=utf-8',
                        'SOAPAction' => 'http://anzctr.org.au/WebServices/AnzctrWebServices/AnzctrTrialDetailsV2' // SOAP Method to post to
                    ]
                ]
            );

            if ($response->getStatusCode() === 200) {
                // Success!
                $xmlString = (string) $response->getBody();
                // by default, we use ro: as a namesapce prefix for everything
                $result = XMLUtil::getElementsByXPath($xmlString, "//ro:AnzctrTrialDetailsV2Result", 'http://anzctr.org.au/WebServices/AnzctrWebServices');
                ANZCTRUtil::validateContent((string) $result[0], $identifier);
                $metadata = (string) $result[0];
            } else {
                throw new Exception("Unable to retrieve anzctr metadata status code:" . $response->getStatusCode());
            }

        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $metadata;
    }







    /**
     * @param $xml
     * @param $identifier
     * Make sure that we have received a trail, and it is the correct one
     * @return void
     * @throws Exception
     */
    private static function validateContent($xml, $identifier){
        $simpleXML = XMLUtil::getSimpleXMLFromString($xml);
        $actrn = $simpleXML->trial->actrn;
        $identifier = substr($identifier, -14);
        $actrn = substr($actrn, -14);
        if($identifier !== $actrn){
            throw new Exception("Requested Trial ID:$identifier not equal to result's ID:$actrn");
        }

    }
}