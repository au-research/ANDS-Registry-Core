<?php

namespace ANDS\API\Task;

use ANDS\Payload;

/**
 * Class ManagePayload
 * @package ANDS\API\Task
 */
trait ManagePayload
{
    private $payloads = [];
    public $skipLoading = false;

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
        return array_key_exists($key,
            $this->payloads) ? $this->payloads[$key] : null;
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
     * @param $fileExtension the file extension we want to load from harvested contents
     * TODO: need a better file searching mechanism than scan_dir
     */

    public function loadPayload($fileExtention = 'xml')
    {
        $this->payloads = [];
        $path = $this->getHarvestedPath();

        $this->log("Payload path: ". $path);
        
        if (!is_dir($path)) {
            $path = $path . '.' . $fileExtention;
            // $this->log('Loading payload from file: ' . $path);
            $this->loadPayloadFromFile($path);
        } else {
            // $this->log('Loading payload from directory: ' . $path);
            $directory = scandir($path);
            $files = array();
            foreach ($directory as $f) {
                if (endsWith($f, '.' . $fileExtention)) {
                    $files[] = $f;
                    $this->loadPayloadFromFile($path.'/'.$f);
                }
            }
        }

        return $this;
    }



    public function skipLoadingPayload()
    {
        $this->skipLoading = true;
        return $this;
    }

    /**
     * Loading a filePath into the payloads
     * TODO: need a better file accessor than file_get_contents
     * @param $filePath
     * @return bool
     */
    private function loadPayloadFromFile($filePath)
    {
        if (!is_file($filePath)) {
            $this->addError('File '. $filePath. " is not accessible");
            return false;
        }

        $payload = new Payload($filePath);

        $this->setPayload(
            $filePath, $payload
        );
        
        if (is_array($this->getTaskData('payloadsInfo')) && in_array($payload->toArray(), $this->getTaskData('payloadsInfo'))) {
            return;
        }

        $this->addTaskData("payloadsInfo", $payload->toArray());
    }

    /**
     * Returns the harvested path
     * given dataSourceID and batchID taskData set
     *
     * @return string
     */
    public function getHarvestedPath()
    {
        $harvestedContentDir = \ANDS\Util\config::get('app.harvested_contents_path');
        
        $harvestedContentDir = rtrim($harvestedContentDir, '/') . '/';
        return $harvestedContentDir . $this->getTaskData('dataSourceID') . '/' . $this->getTaskData('batchID');
    }

    /**
     * Write the payload out to a file
     * TODO: need a better file accessor
     *
     * @param $path
     * @param $content
     */
    public function writePayload($path, $content)
    {
        try {
            file_put_contents($path, $content);
        } catch (Exception $e) {
            $this->addError("Error trying to write to file: ".$path);
        }
    }
}