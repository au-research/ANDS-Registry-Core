<?php
/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith
 */
namespace Abrahamc\TwitterOAuth;

class Consumer
{
    /** @var string  */
    public $key;
    /** @var string  */
    public $secret;
    /** @var string|null  */
    public $callbackUrl;

    /**
     * @param string|null $key
     * @param string|null $secret
     * @param null $callbackUrl
     */
    public function __construct($key = null, $secret = null, $callbackUrl = null)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Consumer[key={$this->key},secret={$this->secret}]";
    }
}