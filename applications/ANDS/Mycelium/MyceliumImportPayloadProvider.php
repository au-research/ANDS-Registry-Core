<?php

namespace ANDS\Mycelium;

use ANDS\Registry\Providers\RegistryContentProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class MyceliumImportPayloadProvider implements RegistryContentProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    public static function get(RegistryObject $record)
    {
        return [
            'registryObjectId' => $record->id,
            'rifcs' => base64_encode($record->getCurrentData()->data),
            'key' => $record->key,
            'type' => $record->type,
            'class' => $record->class,
            'title' => $record->title,
            'list_title' => $record->getRegistryObjectAttributeValue("list_title"),
            'slug' => $record->slug,
            'portalUrl' => $record->portalUrl,
            'status' => $record->status,
            'batchId' => null,
            'group' => $record->group,
            'dataSource' => [
                'id' => $record->datasource->id,
                'title' => $record->datasource->title,
                'key' => $record->datasource->key
            ],
            'additionalRelations' => self::getPrimaryKeysRelationship($record)
        ];
    }

    /**
     * Obtain the Primary Keys relationships for a given record
     * Formatted in Mycelium's additional relationships formatting
     *
     * @param \ANDS\RegistryObject $record
     * @return array
     */
    private static function getPrimaryKeysRelationship(RegistryObject $record)
    {
        $origin = 'PRIMARY-KEY';
        $relations = [];
        $dataSource = $record->datasource;
        $recordClass = $record->class;

        if ($dataSource->attr('create_primary_relationships') != DB_TRUE) {
            return [];
        }

        // primary_key_1
        $primaryKey = $dataSource->attr('primary_key_1');
        $primaryRecord = RegistryObjectsRepository::getPublishedByKey($primaryKey);
        $relationType = $dataSource->attr("${recordClass}_rel_1");
        if ($primaryKey && $primaryRecord && $relationType && $primaryKey != $record->key) {
            $relations[] = [
                'origin' => $origin,
                'toKey' => $primaryRecord->key,
                'relationType' => $relationType
            ];
        }

        // primary_key_2
        $primaryKey = $dataSource->attr('primary_key_2');
        $primaryRecord = RegistryObjectsRepository::getPublishedByKey($primaryKey);
        $relationType = $dataSource->attr("${recordClass}_rel_2");
        if ($primaryKey && $primaryRecord && $relationType && $primaryKey != $record->key) {
            $relations[] = [
                'origin' => $origin,
                'toKey' => $primaryRecord->key,
                'relationType' => $relationType
            ];
        }

        return $relations;
    }
}