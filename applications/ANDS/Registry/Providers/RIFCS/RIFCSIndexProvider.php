<?php


namespace ANDS\Registry\Providers\RIFCS;
use ANDS\RegistryObject;

class RIFCSIndexProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    /**
     * Provides an indexable array for the RIFCS record
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function get(RegistryObject $record)
    {
        if (!self::isIndexable($record)) {
            return [];
        }

        $index = collect([])
            ->merge(CoreMetadataProvider::getIndexableArray($record))
            ->merge(TitleProvider::getIndexableArray($record))
            ->merge(DescriptionProvider::getIndexableArray($record))
            ->merge(IdentifierProvider::getIndexableArray($record))
            ->merge(SubjectProvider::getIndexableArray($record))
            ->merge(MatchingIdentifierProvider::getIndexableArray($record))
            ->toArray();

        // todo tags <- TagsProvider
        // todo access_rights <- AccessRightsProvider?
        // todo related_info_search
        // todo spatial <- SpatialProvider
        // todo temporal <- DatesProvider
        // todo theme_page <- TagsProvider
        // todo grants <- GrantsMetadataProvider
        // todo license_class <- LicenseProvider
        // todo access_methods_ss

        // todo grants <- GrantsMetadataProvider
//        if ($record->class === "activity") {
//            $index = collect($index)->merge(GrantsMetadataProvider::getIndexableArray($record));
//        }

        return $index;
    }

    /**
     * Determine whether the record should be indexed
     *
     * @param RegistryObject $record
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