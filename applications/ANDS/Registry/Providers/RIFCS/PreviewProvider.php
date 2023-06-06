<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;

/**
 *
 * Preview Provider to be used by portal as a service
 *
 */
class PreviewProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    public static function get(RegistryObject $record)
    {
        $preview_content = [];
        $description = DescriptionProvider::get($record);
        $cm = CoreMetadataProvider::getIndexableArray($record);
        $title = TitleProvider::get($record);
        $preview_content['id'] = $record->id;
        $preview_content['description'] = $description['primary_description'];
        $preview_content['type'] = $cm['type'];
        $preview_content['title'] = $title['display_title'];
        $preview_content['identifiers'] = IdentifierProvider::getFormattedIdentifiers($record);
        return $preview_content;
        // TODO: Implement get() method.
    }
}