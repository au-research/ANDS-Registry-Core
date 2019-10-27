<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 4/10/19
 * Time: 2:04 PM
 */

namespace ANDS\Registry\ContentProvider\PURE;
use DOMElement;

class IdentifierProvider
{

    public static function getIdentifier(DOMElement $mdNode){
        $identifiers = [];
        $uuid =  $mdNode->getAttributeNode('uuid');
        if($uuid){
                $identifiers[] = $uuid->nodeValue;
        }
        return $identifiers;
    }



}