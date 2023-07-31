<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class RelatedInfoProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    public static function get(RegistryObject $record)
    {
        // TODO: Implement get() method.

    }

    /**
     * Get the text content of all each relatedInfo elements an array of string
     * @param RegistryObject $record
     * @return array
     */
    public static function getIndexableArray(RegistryObject $record)
    {

        $xml = $record->getCurrentData()->data;
        $related_info_search = XMLUtil::getTextContent($xml, "relatedInfo");

        if(sizeof($related_info_search) > 0){
            return ['related_info_search' => $related_info_search];
        }
        else{
            return [];
        }
    }



    /**
     * Get the raw identifier value  of given relatedInfo type with a given normalised identifier
     * @param Relationship $publication
     * @param RegistryObject $record
     * @param string $type
     * @return string
     */
    public static function getNonNormalisedIdentifier($relationship, $record, $type){

        foreach (XMLUtil::getElementsByXPath($record->getCurrentData()->data,
            'ro:registryObject/ro:' . $record->class . '/ro:relatedInfo[@type="'.$type.'"]/ro:identifier') AS $relatedIdentifier) {
            $string = trim((string) $relatedIdentifier);
            $substring  = trim($relationship['to_identifier']);
            $length = strlen($substring);
            //if the provided normalised identifier is the ending substring of the rifcs provided identifier return
            if ( substr_compare(strtoupper($string), strtoupper($substring), -$length) === 0 ) {
                return $string;
            }
        }
        return $relationship['to_identifier'];
    }


}