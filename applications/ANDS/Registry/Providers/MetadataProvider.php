<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;

class MetadataProvider implements RegistryContentProvider
{

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        return;
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record)
    {
        return self::getSelective($record);
    }

    public static function getSelective(
        RegistryObject $record,
        $only = ['relationships', 'recordData']
    ) {
        $data = [];
        if (in_array('relationships', $only)) {
            $data['relationships'] = RelationshipProvider::getMergedRelationships($record, $includeDuplicate = false);
        }

        if (in_array('duplicates_relationships', $only)) {
            $data['relationships'] = RelationshipProvider::getMergedRelationships($record);
        }

        if (in_array('recordData', $only)) {
            $data['recordData'] = $record->getCurrentData()->data;
        }

        return $data;
    }
}