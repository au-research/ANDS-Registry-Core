<?php


namespace ANDS;

use ANDS\Util\Config;


/**
 * Class Payload
 * @package ANDS
 */
class Payload
{
    private $path;
    private $path_validated;
    private $path_processed;

    /**
     * ImportPayload constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
        $this->init();
    }

    /**
     * Structure the path correctly
     * Set the path if there's a file
     */
    public function init()
    {
        $this->path_validated = is_file($this->path.'.validated') ? $this->path.'.validated' : null;
        $this->path_processed = is_file($this->path.'.processed') ? $this->path.'.processed' : null;
    }

    /**
     * @param $status
     * @return null|string
     */
    public function getContentByStatus($status)
    {
        switch ($status) {
            case "unvalidated":
            case "original":
                return file_get_contents($this->path);
                break;
            case "validated":
                if ($this->path_validated != null) {
                    return file_get_contents($this->path_validated);
                }
                return null;
                break;
            case "processed":
                if ($this->path_processed != null) {
                    return file_get_contents($this->path_processed);
                }
                return null;
                break;
            default:
                return null;
        }

        return null;
    }

    /**
     * Write the content out to a given status
     * usage: writeContentByStatus('validated', $xml)
     * @param $status
     * @param $content
     * @return int
     */
    public function writeContentByStatus($status, $content)
    {
        $file = $this->path.'.'.$status;
        file_put_contents($file, $content);
        chmod($file, 0775);
        $this->init();
        return true;
    }

    /**
     * Given a flat object to easy saving and reinitializing
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'path' => $this->path,
            'path_validated' => $this->path_validated,
            'path_processed' => $this->path_processed
        ];
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Static method to write a payload out to file
     *
     * @param $dataSourceID
     * @param $batchID
     * @param $content
     * @throws \Exception
     */
    public static function write($dataSourceID, $batchID, $content)
    {
        $harvestedContentDir = get_config_item('harvested_contents_path');
        $harvestedContentDir = rtrim($harvestedContentDir, '/') . '/';
        $directory = $harvestedContentDir.$dataSourceID;
        if (!is_dir($directory)) {
            try {
                mkdir($directory, 0755, true); // mkdir 0775 doesn't work
                chmod($directory, 0775);
            } catch (\Exception $e) {
                $message = get_exception_msg($e);
                throw new \Exception("Failure creating $directory: $message");
            }
        }
        $file = $harvestedContentDir.$dataSourceID.'/'.$batchID.'.xml';
        try {
            file_put_contents($file, $content);
            chmod($file, 0775);
        } catch (\Exception $e) {
            $message = get_exception_msg($e);
            throw new \Exception("Failure putting content into $file : $message");
        }
    }

}