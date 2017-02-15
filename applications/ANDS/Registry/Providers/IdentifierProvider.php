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
        $identifiers = static::processIdentifiers($record);
        return $identifiers;

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
        $identifiers = [];
        $xml = $record->getCurrentData()->data;
        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            $identifierValue = trim((string)$identifier;
            if ($identifierValue == "") {
                continue;
            }
            $identifiers[] = $identifierValue;
            Identifier::create(
                [
                    'registry_object_id' => $record->registry_object_id,
                    'identifier' => $identifierValue,
                    'identifier_type' => trim((string)$identifier['type'])
                ]
            );

        }
        return $identifiers;
    }
}