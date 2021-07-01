<?php


namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\Registry\Providers\TitleProvider;
use ANDS\RegistryObject;

class RIFCSIndexProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    /**
     * Provides an indexable array for the RIFCS record
     *
     * @param \ANDS\RegistryObject $record
     * @return array
     */
    public static function get(RegistryObject $record)
    {
        if (!self::isIndexable($record)) {
            return [];
        }

        return collect([])
            ->merge(self::getCoreIndexableValues($record))
            ->merge(self::getTitleIndexableValues($record))
            ->merge(self::getDescriptionIndexableValues($record))
            ->merge(self::getIdentifiersIndexableValues($record))
            ->toArray();
    }

    /**
     * Get the array of indexable titles fields
     *
     * @param \ANDS\RegistryObject $record
     * @return array
     */
    public static function getTitleIndexableValues(RegistryObject $record)
    {
        $titles = TitleProvider::get($record);

        // simplified title if iconv is installed
        $simplifiedTitle = function_exists('iconv')
            ? strip_tags(html_entity_decode(iconv('UTF-8', 'ASCII//TRANSLIT', $titles['list_title']), ENT_QUOTES))
            : null;

        return [
            'title' => $titles['display_title'],
            'display_title' => $titles['display_title'],
            'list_title' => $titles['list_title'],
            'alt_list_title' => $titles['alt_titles'],
            'alt_display_title' => $titles['alt_titles'],
            'simplified_title' => $simplifiedTitle
        ];
    }

    /**
     * Get the array of indexable "core" fields
     *
     * @param \ANDS\RegistryObject $record
     * @return array
     */
    public static function getCoreIndexableValues(RegistryObject $record)
    {
        return [
            'id' => $record->id,
            'slug' => $record->slug,
            'key' => $record->key,
            'status' => $record->status,
            'group' => $record->group,
            'type' => $record->type,
            'class' => $record->class,
            'data_source_id' => $record->dataSource->id,
            'data_source_key' => $record->dataSource->key,
            'record_created_timestamp' => $record->created_at->format('Y-m-d\TH:i:s\Z'),
            'record_modified_timestamp' => $record->modified_at->format('Y-m-d\TH:i:s\Z')
        ];
    }

    /**
     * Obtain indexable descriptions for registryObject
     *
     * @param \ANDS\RegistryObject $record
     * @return array
     */
    public static function getDescriptionIndexableValues(RegistryObject $record)
    {
        $types = [];
        $values = [];

        $descriptions = DescriptionProvider::get($record);
        foreach ($descriptions['descriptions'] as $description) {
            $types[] = $description['type'];
            $values[] = $description['value'];
        }

        $theDescription = $descriptions['primary_description'];

        // list description is the trimmed form of the description
        $listDescription = trim(strip_tags(html_entity_decode(html_entity_decode($theDescription)), ENT_QUOTES));

        //add <br/> for NL if doesn't already have <p> or <br/>
        $theDescription = !(strpos($theDescription, "&lt;br") !== FALSE || strpos($theDescription, "&lt;p") !== FALSE || strpos($theDescription, "&amp;#60;p") !== FALSE) ? nl2br($theDescription) : $theDescription;

        return [
            'description_type' => $types,
            'description_value' => $values,
            'list_description' => $listDescription,
            'description' => $theDescription
        ];
    }


    /**
     * Get indexable values for Identifiers
     *
     * @param \ANDS\RegistryObject $record
     * @return array[]
     */
    public static function getIdentifiersIndexableValues(RegistryObject $record) {
        $identifiers = IdentifierProvider::get($record);

        $types = [];
        $values = [];

        foreach ($identifiers as $identifier) {
            $types[] = $identifier['type'];;
            $values[] = $identifier['value'];
        }

        return [
            'identifier_type' => $types,
            'identifier_value' => $values
        ];
    }

    /**
     * Determine whether the record should be indexed
     *
     * @param \ANDS\RegistryObject $record
     * @return bool
     */
    public static function isIndexable(RegistryObject $record)
    {
        // only index PUBLISHED records
        if ($record->isDraftStatus()) {
            return false;
        }

        // CC-1286. Remove Activity Records from Contributor 'Public Records of Victoria' from RDA
        if ($record->class === "activity" && $record->group === "Public Record Office Victoria") {
            return false;
        }

        return true;
    }
}