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
    protected $schema_prefix = 'https://schema.org/';

    public function init()
    {
        $this->content = null;
        $this->fileExtension = 'json';
        return $this;
    }

    public function loadContent($json)
    {
        $this->content = [];
        $jsonObjects = json_decode($json);
        // some payloads may contain a single json record
        // if an array iterate through
        if (is_array($jsonObjects)) {
            foreach ($jsonObjects as $jo) {
                $record['identifiers'] = IdentifierProvider::getIdentifier($jo);
                $record['nameSpaceURI'] = $this->schema_prefix;
                $record['data'] = json_encode($jo);
                $record['hash'] = md5($record['data']);
                $this->payloadCounter++;
                $this->content[] = $record;
            }
        }
        //otherwise just process the single record
        else{
            $record['identifiers'] = IdentifierProvider::getIdentifier($jsonObjects);
            $record['nameSpaceURI'] = $this->schema_prefix;
            $record['data'] = json_encode($jsonObjects);
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