<?php

namespace ANDS\RegistryObject;

/**
 * Class Access
 * An access for a registryObject
 *
 * @package ANDS\RegistryObject
 */
class Access
{
    public $url;
    public $title = "No Title";
    public $mediaType = "";
    public $byteSize = "";
    public $notes = "Visit Service";

    /**
     * Access constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return Access
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Access
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }

    /**
     * @param string $mediaType
     * @return Access
     */
    public function setMediaType($mediaType)
    {
        $this->mediaType = $mediaType;
        return $this;
    }

    /**
     * @return string
     */
    public function getByteSize()
    {
        return $this->byteSize;
    }

    /**
     * @param string $byteSize
     * @return Access
     */
    public function setByteSize($byteSize)
    {
        $this->byteSize = $byteSize;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     * @return Access
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

}