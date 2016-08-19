<?php

namespace ANDS\API\DOI;

/**
 * A Client that interfaces with datacite.org
 * Class DataCiteClient
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ANDS\API\DOI
 */
class DataCiteClient
{

    private $username;
    private $password;
    private $dataciteUrl = 'https://mds.test.datacite.org/';

    private $errors = array();
    private $messages = array();

    /**
     * DataCiteClient constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * get the URL content of a DOI by ID
     * @param $doiId
     * @return mixed
     */
    public function get($doiId)
    {
        return $this->request($this->dataciteUrl . 'doi/' . $doiId);
    }

    /**
     * get the Metadata of a DOI by ID
     * @param $doiId
     * @return mixed
     */
    public function getMetadata($doiId)
    {
        return $this->request($this->dataciteUrl . 'metadata/' . $doiId);
    }


    public function mint($doiId, $doiUrl, $xmlBody = false)
    {
        //update xml first
        $this->update($xmlBody);

        // and then mint
        $this->request($this->dataciteUrl . 'doi/',
            "doi=" . $doiId . "\nurl=" . $doiUrl);

        return $this->hasError() ? false : true;
    }

    /**
     * Update XML
     * @param bool|false $xmlBody
     * @return mixed
     */
    public function update($xmlBody = false)
    {
        return $this->request($this->dataciteUrl . 'metadata/', $xmlBody);
    }

    public function activate()
    {

    }

    public function deActivate($doiId)
    {
        return $this->request($this->dataciteUrl . 'metadata/' . $doiId, false,
            "DELETE");
    }

    /**
     * @return string
     */
    public function getDataciteUrl()
    {
        return $this->dataciteUrl;
    }

    /**
     * @param string $dataciteUrl
     * @return $this
     */
    public function setDataciteUrl($dataciteUrl)
    {
        $this->dataciteUrl = $dataciteUrl;
        return $this;
    }

    /**
     * Do an actual request to the specified URL
     *
     * @todo don't use curl, use guzzle
     * @param $url
     * @param bool $content
     * @param bool $customRequest
     * @return mixed
     */
    private function request($url, $content = false, $customRequest = false)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_HEADER, true);

        curl_setopt($ch, CURLOPT_USERPWD,
            $this->username . ":" . $this->password);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-Type:application/xml;charset=UTF-8"));

        if ($content) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }

        if ($customRequest) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
        }

        $output = curl_exec($ch);
        $outputINFO = curl_getinfo($ch);

        if ($outputINFO['http_code'] >= 400) {
            $this->log(curl_error($ch), "error");
            $this->log($output, "error");
        } else {
            $this->log($output);
        }

        curl_close($ch);
        return $output;
    }

    private function log($content, $context = "info")
    {
        if ($content === "" || !$content) {
            return;
        }
        if ($context == "error") {
            $this->errors[] = $content;
        } else {
            if ($context == "info") {
                $this->messages[] = $content;
            }
        }
    }

    public function getResponse()
    {
        return [
            'errors' => $this->getErrors(),
            'messages' => $this->getMessages()
        ];
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return count($this->getErrors()) > 0 ? true : false;
    }
}