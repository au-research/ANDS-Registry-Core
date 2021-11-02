<?php

namespace ANDS\Mycelium;

use ANDS\DataSource;
use ANDS\Repository\RegistryObjectsRepository;

class MyceliumDataSourcePayloadProvider
{
    public static function get(DataSource $dataSource)
    {
        return [
            "id" => $dataSource->id,
            "title" => $dataSource->title,
            'primaryKeySetting' => self::getPrimaryKeySettings($dataSource)
        ];
    }

    public static function getPrimaryKeySettings(DataSource $dataSource) {
        $primaryKeys = [];

        if (! $dataSource->attr("create_primary_relationships")) {
            return [
                'enabled' => false,
                'primaryKeys' => []
            ];
        }

        for ($i=1;$i<3;$i++) {
            $primaryKey1 = $dataSource->attr("primary_key_$i");
            if ($primaryKey1) {
                $primaryKeys[] = [
                    'key' => $primaryKey1,
                    'relationTypeFromCollection' => $dataSource->attr("collection_rel_$i") ?: null,
                    'relationTypeFromService' => $dataSource->attr("service_rel_$i") ?: null,
                    'relationTypeFromActivity' => $dataSource->attr("activity_rel_$i") ?: null,
                    'relationTypeFromParty' => $dataSource->attr("party_rel_$i") ?: null
                ];
            }
        }

        return [
            'enabled' => true,
            'primaryKeys' => $primaryKeys
        ];
    }
}