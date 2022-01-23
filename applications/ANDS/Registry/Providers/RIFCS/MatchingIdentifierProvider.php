<?php


namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;

class MatchingIdentifierProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    public static function get(RegistryObject $record)
    {
        $identifiermatch = [];
        $myceliumServiceClient = new \ANDS\Mycelium\MyceliumServiceClient(\ANDS\Util\Config::get('mycelium.url'));
        $duplicates =   json_decode($myceliumServiceClient->getDuplicateRecords($record->id));
            // the MyceliumService will also return the original record in the list so remove it
            foreach ($duplicates as $duplicate) {
                if ($duplicate->identifier != $record->id) {
                    $identifiermatch[] = $duplicate->identifier;
                };
            }
        return  $identifiermatch;
    }

    /**
     * Obtain an associative array for the indexable fields
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getIndexableArray(RegistryObject $record)
    {
        return [
            'identical_record_ids' => self::get($record)
        ];

    }
}