<?php


namespace ANDS\Registry\Providers;

use ANDS\RegistryObject;
use ANDS\RegistryObject\Identifier;
use ANDS\Util\XMLUtil;


/**
 * Class IdentifierProvider
 * TODO: refactor into RIFCSProvider
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
     * TODO: Refactor to use self::get()
     * @param RegistryObject $record
     */
    public static function processIdentifiers(RegistryObject $record)
    {
        $identifiers = [];
        $xml = $record->getCurrentData()->data;
        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            $identifierValue = trim((string)$identifier);
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

    public static function get(RegistryObject $record, $xml = null)
    {
        if (!$xml) {
            $xml = $record->getCurrentData()->data;
        }

        $identifiers = [];

        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            $identifierValue = trim((string)$identifier);
            if ($identifierValue == "") {
                continue;
            }
            $identifiers[] = [
                'value' => $identifierValue,
                'type' => trim((string)$identifier['type'])
            ];
        }
        return $identifiers;
    }
}