<?php


namespace ANDS\Registry\Providers;

use ANDS\RegistryObject;
use ANDS\RegistryObject\Identifier;
use ANDS\Util\XMLUtil;


/**
 * Class IdentifierProvider
 * @package ANDS\Registry\Providers
 */
class IdentifierProvider
{
    /**
     * Add all Identifiers from rifcs
     * @param RegistryObject $record
     */
    public static function process(RegistryObject $record)
    {
        static::deleteAllIdentifiers($record);
        static::processIdentifiers($record);

    }


    /**
     * Delete Identifiers
     * Clean up before a processing
     *
     * @param RegistryObject $record
     */
    public static function deleteAllIdentifiers(RegistryObject $record)
    {
        Identifier::where('registry_object_id', $record->registry_object_id)->delete();
    }


    /**
     * Create Identifiers from current RIFCS
     *
     * @param RegistryObject $record
     */
    public static function processIdentifiers(RegistryObject $record)
    {

        $xml = $record->getCurrentData()->data;
        foreach (XMLUtil::getElementsByXPath($xml, 'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            Identifier::create(
                ['registry_object_id'=>$record->registry_object_id,
                    'identifier'=>trim((string) $identifier),
                    'identifier_type'=> trim((string) $identifier['type'])
                ]
            );

        }
    }
}