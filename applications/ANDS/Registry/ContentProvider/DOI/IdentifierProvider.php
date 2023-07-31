<?php

namespace ANDS\Registry\ContentProvider\DOI;

use DOMDocument, DOMElement;

class IdentifierProvider
{

    public static function process($json)
    {


    }

    public static function getIdentifiers(DOMElement $mdNode){
        $identifiers = [];
        $fileIdentifiers =  $mdNode->getElementsByTagName('identifier');
        if(sizeof($fileIdentifiers) > 0){
            foreach ($fileIdentifiers as $fileIdentifier){
                $identifiers[] = array("identifier"=>$fileIdentifier->nodeValue, "identifier_type"=>strtolower($fileIdentifier->getAttribute('identifierType')));
            }
        }
        return $identifiers;
    }

}