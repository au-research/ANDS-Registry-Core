<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;

class ScholixProvider implements RegistryContentProvider
{
    /**
     * if the record is a collection
     * and is related to a type of publication
     *
     * @param RegistryObject $record
     * @param null $relationships
     * @return bool
     */
    public static function isScholixable(RegistryObject $record, $relationships = null)
    {
        // early return if it's not a collection
        if ($record->class != "collection") {
            return false;
        }

        // search through combined relationships to see if there's a related publication
        if (!$relationships) {
            $relationships = RelationshipProvider::getMergedRelationships($record);
        }

        $types = collect($relationships)->map(function($item) {
            return $item->prop('to_related_info_type') ?: $item->prop('to_type');
        })->toArray();

        if (!in_array('publication', $types)) {
            return false;
        }

        return true;
    }

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $object
     * @return mixed
     */
    public static function process(RegistryObject $object)
    {
        // TODO: Implement process() method.
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $object
     * @return mixed
     */
    public static function get(RegistryObject $object)
    {
        // TODO: Implement get() method.
    }
}