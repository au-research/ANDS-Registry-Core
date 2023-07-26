<?php

namespace Abraham\TwitterOAuth;

/**
 * Handle setting and storing config for TwitterOAuth.
 *
 * @author Abraham Williams <abraham@abrah.am>
 */
class Config
{
    // Update extension function when updating this list.
    const SUPPORTED_VERSIONS = ['1.1', '2'];
    /** @var int How long to wait for a response from the API */
    protected $timeout = 5;
    /** @var int how long to wait while connecting to the API */
    protected $connectionTimeout = 5;
    /** @var int How many times we retry request when API is down */
    protected $maxRetries = 0;
    /** @var int Delay in seconds before we retry the request */
    protected $retriesDelay = 1;
    /** @var string Version of the Twitter API requests should target */
    protected $apiVersion = '2';

    /**
     * Decode JSON Response as associative Array
     *
     * @see http://php.net/manual/en/function.json-decode.php
     *
     * @var bool
     */
    protected $decodeJsonAsArray = false;
    /** @var string User-Agent header */
    protected $userAgent = 'TwitterOAuth (+https://twitteroauth.com)';
    /** @var array Store proxy connection details */
    protected $proxy = [];

    /** @var bool Whether to encode the curl requests with gzip or not */
    protected $gzipEncoding = true;

    /** @var integer Size for Chunked Uploads */
    protected $chunkSize = 250000; // 0.25 MegaByte

    /**
     * Set the  Twitter API version.
     *
     * @param string $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        if (in_array($apiVersion, self::SUPPORTED_VERSIONS, true)) {
            $this->apiVersion = $apiVersion;
        } else {
            throw new TwitterOAuthException('Unsupported API version');
        }
    }

    /**
     * Set the connection and response timeouts.
     *
     * @param int $connectionTimeout
     * @param int $timeout
     */
    public function setTimeouts($connectionTimeout, $timeout)
    {
        $this->connectionTimeout = $connectionTimeout;
        $this->timeout = $timeout;
    }

    /**
     *  Set the number of times to retry on error and how long between each.
     *
     * @param int $maxRetries
     * @param int $retriesDelay
     */
    public function setRetries($maxRetries, $retriesDelay)
    {
        $this->maxRetries = $maxRetries;
        $this->retriesDelay = $retriesDelay;
    }

    /**
     * @param bool $value
     */
    public function setDecodeJsonAsArray($value)
    {
        $this->decodeJsonAsArray = $value;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @param array $proxy
     */
    public function setProxy(array $proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * Whether to encode the curl requests with gzip or not.
     *
     * @param boolean $gzipEncoding
     */
    public function setGzipEncoding($gzipEncoding)
    {
        $this->gzipEncoding = $gzipEncoding;
    }

    /**
     * Set the size of each part of file for chunked media upload.
     *
     * @param int $value
     */
    public function setChunkSize($value)
    {
        $this->chunkSize = $value;
    }
}