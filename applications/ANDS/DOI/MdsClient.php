<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 4/6/18
 * Time: 11:29 AM
 */

namespace ANDS\DOI;


class MdsClient implements DataCiteClient
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
    public function __construct($username, $password, $testPassword)
    {
        $this->username = $username;
        $this->password = $password;
        $this->testPassword = $testPassword;
        //dd($this->username,$this->password, $this->testPassword);
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
        $this->request($this->dataciteUrl . 'metadata/', $xmlBody);
        return $this->hasError() ? false : true;
    }

    /**
     * UpdateURL
     * @param string $doiUrl, string $doiId
     * @return bool
     */

    public function updateURL($doiId,$doiUrl)
    {
        $this->request($this->dataciteUrl . 'doi/',
            "doi=" . $doiId . "\nurl=" . $doiUrl);
        return $this->hasError() ? false : true;
    }


    ///Don't have an activate function...updating the xml activates a deactivated doi...
    public function activate($xmlBody = false)
    {
        $this->request($this->dataciteUrl . 'metadata/', $xmlBody);

        return $this->hasError() ? false : true;
    }

    public function deActivate($doiId)
    {
        $this->request($this->dataciteUrl . 'metadata/' . $doiId, false,
            "DELETE");

        return $this->hasError() ? false : true;
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
    public function request($url, $content = false, $customRequest = false)
    {

        $request_parts = explode('/', $url);
        $request = $request_parts[3];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(strpos($url,'test')){
            curl_setopt($ch, CURLOPT_USERPWD,
                $this->username . ":" . $this->testPassword);
        }else{
            curl_setopt($ch, CURLOPT_USERPWD,
                $this->username . ":" . $this->password);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-Type:application/xml;charset=UTF-8"));

        if ($content && !$customRequest) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }

        if ($customRequest) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
        }

        $output = curl_exec($ch);

        $outputINFO = curl_getinfo($ch);


        $this->log([
            "httpcode" => $outputINFO['http_code'],
            "url" => $url,
            "endpoint" => $request,
            "output" => $output
        ]);

        if ($outputINFO['http_code'] >= 400 || curl_errno($ch)) {
            $this->log(curl_error($ch), "error");
            $this->log($output, "error");
        } else {
            $this->log(".datacite.".$request.".response:".$output);
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

    public function clearResponse()
    {
        $this->errors = [];
        $this->messages = [];
        return $this;
    }
}