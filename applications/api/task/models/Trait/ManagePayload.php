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

    /**
     * Does this task has a payload
     * TODO: check for actual registryObject inside the payload
     * @return bool
     */
    public function hasPayload()
    {
        if (count($this->getPayloads()) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Load the payload specified in the parent task
     * to the parent payloads array
     * TODO: need a better file accessor than file_get_contents
     */
    public function loadPayload()
    {
        $this->payloads = [];
        $harvestedContentDir = get_config_item('harvested_contents_path');
        $path = $harvestedContentDir . '/' . $this->dataSourceID . '/' . $this->batchID;

        if (!is_dir($path)) {
            $path = $path . '.xml';
            if (is_file($path)) {
                $this->setPayload(
                    $path, file_get_contents($path)
                );
            }
        } else {
            $directory = scandir($path);
            $files = array();
            foreach ($directory as $f) {
                if (endsWith($f, '.xml')) {
                    $files[] = $f;
                }
            }
            foreach ($files as $index => $f) {
                $this->setPayload(
                    $f, file_get_contents($path . '/' . $f)
                );
            }
        }

        return $this;
    }
}