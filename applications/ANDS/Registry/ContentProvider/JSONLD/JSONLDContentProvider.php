<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 23/9/19
 * Time: 11:51 AM
 */

namespace ANDS\Registry\ContentProvider\JSONLD;


use ANDS\Registry\ContentProvider\MetadataContentProvider;
use ANDS\Registry\ContentProvider\JSONLD\IdentifierProvider;

class JSONLDContentProvider extends MetadataContentProvider
{
    protected $content = [];
    protected $fileExtension = 'json';
    protected $payloadCounter = 0;
    protected $errors = [];
    protected $schema_prefix = 'http://schema.org';

    public function init()
    {
        $this->content = null;
        $this->fileExtension = 'json';
        return $this;
    }

    public function loadContent($json)
    {

        $jsonObjects = json_decode($json);
        $context = $jsonObjects[0]->{'@context'};

        foreach($jsonObjects as $jo){
            $record['identifiers'] = IdentifierProvider::getIdentifier($jo);
            $record['nameSpaceURI'] = $context;
            $record['data'] = json_encode($jo);
            $record['hash'] = md5($record['data']);
            $this->payloadCounter++;
            $this->content[] = $record;
        }

    }



    public function getContent()
    {
        return $this->content;
    }

}