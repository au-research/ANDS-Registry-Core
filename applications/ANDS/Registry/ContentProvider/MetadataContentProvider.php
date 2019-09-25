<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 23/9/19
 * Time: 11:52 AM
 */

namespace ANDS\Registry\ContentProvider;


abstract class MetadataContentProvider
{
    protected $fileExtension = '.tmp';
    protected $payloadCounter = 0;
    protected $content = [];
    protected $errors = [];

    abstract public function init();

    /**
     * Returns the metadata extraction content from this provider and the content
     *
     * @return mixed
     */

    abstract function loadContent($fileContent);

    public function getContent()
    {
        return $this->content;
    }

    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    public function hasMultipleContent()
    {
        return count($this->content);
    }

}