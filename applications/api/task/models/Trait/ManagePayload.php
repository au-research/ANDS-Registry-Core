<?php

namespace ANDS\API\Task;

/**
 * Class ManagePayload
 * @package ANDS\API\Task
 */
trait ManagePayload
{
    private $payloads = [];

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setPayload($key, $value)
    {
        $this->payloads[$key] = $value;
        return $this;
    }

    /**
     * @param bool $key
     * @return mixed
     */
    public function getPayload($key = false)
    {
        return array_key_exists($key, $this->payloads) ? $this->payloads[$key] : null;
    }

    /**
     * Return the first payload found
     *
     * @return mixed
     */
    public function getFirstPayload()
    {
        return array_first($this->payloads);
    }

    /**
     * Get all payload as an array
     *
     * @return array
     */
    public function getPayloads()
    {
        return $this->payloads;
    }

    /**
     * Delete a particular payload by key
     *
     * @param $key
     * @return $this
     */
    public function deletePayload($key)
    {
        unset($this->payloads[$key]);
        return $this;
    }
}