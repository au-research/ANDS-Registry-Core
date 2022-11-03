<?php

namespace ANDS\DOI;

/**
 * A Client that interfaces with datacite.org
 * Class DataCiteClient
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @package ANDS\API\DOI
 */
Interface DataCiteClient
{
    /**
     * DataCiteClient constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password, $testPassword);
   

    /**
     * get the URL content of a DOI by ID
     * @param $doiId
     * @return mixed
     */
    public function get($doiId);


    /**
     * get the Metadata of a DOI by ID
     * @param $doiId
     * @return mixed
     */
    public function getMetadata($doiId);



    public function mint($doiId, $doiUrl, $xmlBody = false);


    /**
     * Update XML
     * @param bool|false $xmlBody
     * @return mixed
     */
    public function update($xmlBody = false);

    /**
     * UpdateURL
     * @param string $doiUrl, string $doiId
     * @return bool
     */

    public function updateURL($doiId,$doiUrl);


    ///Don't have an activate function...updating the xml activates a deactivated doi...
    public function activate($xmlBody = false);


    public function deActivate($doiId);


    /**
     * @return string
     */
    public function getDataciteUrl();
    /**
     * @param string $dataciteUrl
     * @return $this
     */
    public function setDataciteUrl($dataciteUrl);
    
    public function getResponse();

    /**
     * @return array
     */
    public function getMessages();

    /**
     * @return array
     */
    public function getErrors();

    /**
     * @return bool
     */
    public function hasError();
}