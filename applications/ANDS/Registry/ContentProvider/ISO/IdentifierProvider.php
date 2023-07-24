<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 23/9/19
 * Time: 2:20 PM
 */

namespace ANDS\Registry\ContentProvider\ISO;
use DOMDocument, DOMElement;

class IdentifierProvider
{

    public static function process($json)
    {


    }

    public static function getIdentifier(DOMElement $mdNode){
        $identifiers = [];
        $created = false;
        $fileIdentifiers =  $mdNode->getElementsByTagName('fileIdentifier');
        if(sizeof($fileIdentifiers) > 0){
            foreach ($fileIdentifiers as $fileIdentifier){
                $identifiers[] = ['identifier' => trim($fileIdentifier->nodeValue), 'identifier_type' => 'global'];
            }
        }

        if(sizeof($identifiers) == 0){
            $mdIdentifiers = $mdNode->getElementsByTagName('MD_Identifier');
            if(sizeof($mdIdentifiers) > 0){

                /** @var $mdIdentifiers DOMElement[] */
                foreach ($mdIdentifiers as $mdIdentifier){
                    if(sizeof($mdIdentifier->getElementsByTagName('codeSpace')) > 0) {
                        $cspElement = $mdIdentifier->getElementsByTagName('codeSpace')[0];
                        if ($cspElement != null && trim($cspElement->nodeValue) != '' && trim($cspElement->nodeValue) == 'urn:uuid') {
                            $identifiers[] = ['identifier' => trim($mdIdentifier->getElementsByTagName('code')[0]->nodeValue),
                                'identifier_type' => trim($cspElement->nodeValue)];
                        }
                    }
                }
            }
        }

        return $identifiers;
    }

}