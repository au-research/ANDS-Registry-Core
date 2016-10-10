<?php


namespace ANDS;


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
        file_put_contents($this->path.'.'.$status, $content);
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

}