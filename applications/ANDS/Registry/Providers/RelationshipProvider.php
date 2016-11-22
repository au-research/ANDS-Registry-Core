<?php


namespace ANDS\Registry\Providers;


use ANDS\RecordData;
use ANDS\Registry\Connections;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\RegistryObject\Relationship;
use ANDS\RegistryObject\IdentifierRelationship;
use ANDS\Util\XMLUtil;


/**
 * Class RelationshipProvider
 * @package ANDS\Registry\Providers
 */
class RelationshipProvider
{
    /**
     * Add all relationship from
     * explicit in rifcs
     * identifier relationships
     * primary keys relationships
     *
     * @param RegistryObject $record
     */

    public static function process(RegistryObject $record)
    {
        static::deleteAllRelationshipsFromId($record->registry_object_id);

        $recordData = $record->getCurrentData();

        $explicit_keys = static::processRelatedObjects($record, $recordData->data);
        static::processRelatedInfos($record, $recordData->data);
        static::createPrimaryLinks($record, $explicit_keys);

    }

    public static function deleteAllRelationshipsFromId($registry_object_id){
        RegistryObjectsRepository::deleteIdentifierRelationships($registry_object_id);
        RegistryObjectsRepository::deleteRelationships($registry_object_id);
    }

    public static function createPrimaryLinks($record, $explicit_keys){

        $dataSource = DataSourceRepository::getByID($record->data_source_id);

        if (!$dataSource || $dataSource->getDataSourceAttributeValue('create_primary_relationships') != DB_TRUE) {
            return;
        }

        $primary_key_1 = $dataSource->getDataSourceAttributeValue('primary_key_1');
        $primaryRecord = RegistryObjectsRepository::getPublishedByKey($primary_key_1);

        if ($primaryRecord &&
            $primary_key_1 != $record->key &&
            !in_array($primary_key_1, $explicit_keys)
        ) {
            $explicit_keys[] = $primary_key_1;

            $relationType = $dataSource->getDataSourceAttributeValue($record->class . '_rel_1');
            $this_relationship = format_relationship($record->class, $relationType, PRIMARY_RELATIONSHIP, $primaryRecord->class);
            dd($this_relationship);
            Relationship::create([
                "registry_object_id" => $record->registry_object_id,
                "related_object_key" => $primary_key_1,
                "relation_type" => $this_relationship,
                "relation_description" => "",
                "relation_url" => "",
                "origin" => PRIMARY_RELATIONSHIP
            ]);
        }

        $primary_key_2 = $dataSource->getDataSourceAttributeValue('primary_key_2');
        $primaryRecord = RegistryObjectsRepository::getPublishedByKey($primary_key_2);

        if ($primaryRecord &&
            $primary_key_2 != $record->key &&
            !in_array($primary_key_2, $explicit_keys)
        ) {
            $relationType = $dataSource->getDataSourceAttributeValue($record->class . '_rel_2');;
            $this_relationship = format_relationship($record->class, $relationType, PRIMARY_RELATIONSHIP, $primaryRecord->class);
            Relationship::create([
                "registry_object_id" => $record->registry_object_id,
                "related_object_key" => $primary_key_2,
                "relation_type" => $this_relationship,
                "relation_description" => "",
                "relation_url" => "",
                "origin" => PRIMARY_RELATIONSHIP
            ]);
        }
    }

    public static function processRelatedObjects($record, $xml){
        foreach (XMLUtil::getElementsByName($xml, 'relatedObject') AS $related_object) {
            foreach ($related_object->relation as $arelation) {
                $explicit_keys[] = trim((string)$related_object->key);
                $relationShip = Relationship::create([
                    "registry_object_id" => (string) $record->registry_object_id,
                    "related_object_key" => trim((string) $related_object->key),
                    "relation_type" => (string) $arelation['type'],
                    "relation_description" => (string) $arelation->description,
                    "relation_url" => (string) $arelation->url,
                    "origin" => 'EXPLICIT'
                ]);
            }
        }
        return $explicit_keys;
    }

    public static function processRelatedInfos($record, $xml){

        $processedTypesArray = array('collection', 'party', 'service', 'activity', 'publication', 'website');

        foreach (XMLUtil::getElementsByName( $xml , 'relatedInfo') AS $related_info) {

            $related_info_type = (string)$related_info['type'];
            if (in_array($related_info_type, $processedTypesArray)) {
                $related_info_title = (string)$related_info->title;
                $relation_type = "";
                $related_description = "";
                $related_url = "";
                $relation_type_disp = "";
                $connections_preview_div = "";
                if ($related_info->relation) {
                    foreach ($related_info->relation as $r) {
                        $relation_type .= (string)$r['type'] . ", ";
                        $relation_type_disp .= format_relationship($record->class, (string)$r['type'], 'IDENTIFIER',
                                $related_info_type) . ", ";

                        if ($related_url == '' && (string)$r->url != '') {
                            $related_url = (string)$r->url;
                        }
                        $urlStr = trim((string)$r->url);
                        if ((string)$r->description != '' && (string)$r->url != '') {
                            $connections_preview_div .= "<div class='description'><p>" . (string)$r->description . '<br/><a href="' . $urlStr . '">' . (string)$r->url . "</a></p></div>";
                        }
                    }
                    $relation_type = substr($relation_type, 0, strlen($relation_type) - 2);
                    $relation_type_disp = substr($relation_type_disp, 0, strlen($relation_type_disp) - 2);
                }
                $identifiers_div = "";
                $identifier_count = 0;
                foreach ($related_info->identifier as $i) {
                    $identifiers_div .= getResolvedLinkForIdentifier((string)$i['type'], trim((string)$i));
                    $identifier_count++;
                }
                $identifiers_div = "<h5>Identifier" . ($identifier_count > 1 ? 's' : '') . ": </h5>" . $identifiers_div;
                if ($related_info->notes) {
                    $connections_preview_div .= '<p>Notes: ' . (string)$related_info->notes . '</p>';
                }
                $imgUrl = asset_url('img/' . $related_info_type . '.png', 'base');
                $classImg = '<img class="icon-heading" src="' . $imgUrl . '" alt="' . $related_info_type . '" style="width:24px; float:right;">';
                $connections_preview_div = '<div class="previewItemHeader">' . $relation_type_disp . '</div>' . $classImg . '<h4>' . $related_info_title . '</h4><div class="post">' . $identifiers_div . "<br/>" . $connections_preview_div . '</div>';

                foreach ($related_info->identifier as $i) {
                    if (trim((string)$i) != '') {
                        IdentifierRelationship::create([
                                "registry_object_id" => $record->registry_object_id,
                                "related_object_identifier" => trim((string)$i),
                                "related_info_type" => $related_info_type,
                                "related_object_identifier_type" => (string)$i['type'],
                                "relation_type" => $relation_type,
                                "related_title" => $related_info_title,
                                "related_description" => $related_description,
                                "related_url" => $related_url,
                                "connections_preview_div" => $connections_preview_div,
                                "notes" => (string) $related_info->notes
                            ]
                        );
                    }
                }
            }
        }

    }



    /**
     * Save/cache the grants relationship
     * going upward from the tree of grants network
     *
     * @param RegistryObject $record
     */
    public static function processGrantsRelationship(RegistryObject $record)
    {
        $provider = GrantsConnectionsProvider::create();

        // find funder and saved it, getFunder is recursive by default
        $record->deleteRegistryObjectMetadata('funder_id');
        if ($funder = $provider->getFunder($record)) {
            $record->setRegistryObjectMetadata('funder_id', $funder->registry_object_id);
        }

        // find all parents collections
        $record->deleteRegistryObjectMetadata('parents_collection_ids');
        if ($collections = $provider->getParentsCollections($record)) {
            $record->setRegistryObjectMetadata(
                'parents_collection_ids',
                implode(',', collect($collections)
                    ->pluck('registry_object_id')
                    ->unique()
                    ->toArray()
                )
            );
        }

        // find all parents activities
        $record->deleteRegistryObjectMetadata('parents_activity_ids');
        if ($activities = $provider->getParentsActivities($record)) {
            $record->setRegistryObjectMetadata(
                'parents_activity_ids',
                implode(',', collect($activities)->pluck('registry_object_id')->unique()->toArray())
            );
        }



    }

    /**
     * Returns a list of directly related relationships
     * from this record
     * includes Primary Relationships
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getDirectRelationship(RegistryObject $record)
    {
        // TODO: use Connections Provider to get these data
        $provider = Connections::getStandardProvider();

        // directly related
        $relations = $provider
            ->setFilter('from_key', $record->key)
            ->get();
        return $relations;
    }

    public static function getReverseRelationship(RegistryObject $record)
    {
        $provider = Connections::getStandardProvider();

        // reverse related
        $relations = $provider
            ->setFilter('to_key', $record->key)
            ->setReverse(true)
            ->get();

        return $relations;
    }

    /**
     * Returns a list of grants network related relationships
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getGrantsRelationship(RegistryObject $record)
    {
        // funder
        if ($funderMetadata = $record->getRegistryObjectMetadata('funder_id')) {
            $funderID = $funderMetadata->value;
            $funder = RegistryObjectsRepository::getRecordByID($funderID);
        }

        // parents_activities
        if ($parentsActivityMetadata = $record->getRegistryObjectMetadata('parents_activity_ids')) {
            $parentsActivityIDs = $parentsActivityMetadata->value;
            $parentsActivities = RegistryObject::whereIn('registry_object_id', explode(',', $parentsActivityIDs))->get()->toArray();
        }

        // parents_collection_id
        if ($parentsCollectionIDs = $record->getRegistryObjectMetadata('parents_collection_ids')) {
            $parentsCollectionIDs = $parentsCollectionIDs->value;
            $parentsCollections = RegistryObject::whereIn('registry_object_id', explode(',', $parentsCollectionIDs))->get()->toArray();
        }

        return [
            'funder' => isset($funder) ? $funder : null,
            'parents_activities' => isset($parentsActivities) ? $parentsActivities : [],
            'parents_collections' => isset($parentsCollections) ? $parentsCollections : []
        ];
    }
}