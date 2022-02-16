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


}