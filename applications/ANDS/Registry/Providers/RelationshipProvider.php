<?php


namespace ANDS\Registry\Providers;


use ANDS\RecordData;
use ANDS\Registry\Connections;
use ANDS\Registry\ImplicitRelationshipView;
use ANDS\RegistryObject;
use ANDS\RegistryObject\ImplicitRelationship;
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

        // process explicit relationship from RIFCS
        // create Primary links based on those Explicit Keys
        $explicitKeys = static::processRelatedObjects($record, $recordData->data);
        static::createPrimaryLinks($record, $explicitKeys);

        // relatedInfo relationships
        static::processRelatedInfos($record, $recordData->data);

        // process implicit relationships for the grants network
        static::processGrantsRelationship($record);
    }

    /**
     * Get All Relationships
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function get(RegistryObject $record)
    {
        $allRelationships = [
            'explicit' => static::getDirectRelationship($record),
            'reverse' => static::getReverseRelationship($record),
            'implicit' => static::getImplicitRelationship($record),
            'reverse_implicit' => static::getReverseImplicitRelationship($record),
            'identifier' => static::getIdentifierRelationship($record)
        ];

        return $allRelationships;
    }

    /**
     * @param RegistryObject $record
     * @return array
     */
    public static function getMergedRelationships(RegistryObject $record)
    {
        $allRelationships = static::get($record);
        $allRelationships = collect($allRelationships)->flatten(1)->values()->all();
        $result = [];
        foreach ($allRelationships as $relation) {
            $key = md5($relation->prop('to_key'), $relation->prop('from_key'));
            if (array_key_exists($key, $result)) {
                $result[$key]->mergeWith($relation->getProperties());
            } else {
                $result[$key] = $relation;
            }
        }
        return $result;
    }

    /**
     * Delete Relationship and IdentifierRelationships
     * Clean up before a processing
     *
     * @param $registry_object_id
     */
    public static function deleteAllRelationshipsFromId($registry_object_id)
    {
        RegistryObjectsRepository::deleteIdentifierRelationships($registry_object_id);
        RegistryObjectsRepository::deleteRelationships($registry_object_id);
    }

    /**
     * Generate (Explicit) Relationship for Data Source primary link
     *
     * TODO: generate tests and refactor
     *
     * @param $record
     * @param $explicit_keys
     */
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

    /**
     * Process Explicit RelatedObjects
     *
     * @param $record
     * @param bool $xml
     * @return array
     */
    public static function processRelatedObjects($record, $xml = false){

        if (!$xml) {
            $xml = $record->getCurrentData();
        }

        $explicitKeys = [];
        foreach (XMLUtil::getElementsByName($xml,
            'relatedObject') AS $related_object) {
            foreach ($related_object->relation as $arelation) {
                $explicitKeys[] = trim((string)$related_object->key);
                Relationship::create([
                    "registry_object_id" => (string)$record->registry_object_id,
                    "related_object_key" => trim((string)$related_object->key),
                    "relation_type" => (string)$arelation['type'],
                    "relation_description" => (string)$arelation->description,
                    "relation_url" => (string)$arelation->url,
                    "origin" => 'EXPLICIT'
                ]);
            }
        }
        return $explicitKeys;
    }

    /**
     * Create IdentifierRelationship based on relatedInfo in the RIFCS
     *
     * TODO: Generate tests and refactor
     *
     * @param $record
     * @param bool $xml
     */
    public static function processRelatedInfos($record, $xml = false){

        if (!$xml) {
            $xml = $record->getCurrentData();
        }

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
            ImplicitRelationship::firstOrCreate([
                'from_id' => $record->registry_object_id,
                'to_id' => $funder->registry_object_id,
                'relation_type' => 'isFundedBy',
                'relation_origin' => 'GRANTS'
            ]);
        }

        // find all parents collections
        $record->deleteRegistryObjectMetadata('parents_collection_ids');
        if ($collections = $provider->getParentsCollections($record)) {
            foreach ($collections as $collection) {
                ImplicitRelationship::firstOrCreate([
                    'from_id' => $record->registry_object_id,
                    'to_id' => $collection->registry_object_id,
                    'relation_type' => 'isPartOf',
                    'relation_origin' => 'GRANTS'
                ]);
            }
        }

        // find all parents activities
        $record->deleteRegistryObjectMetadata('parents_activity_ids');
        if ($activities = $provider->getParentsActivities($record)) {
            foreach ($activities as $activity) {
                ImplicitRelationship::firstOrCreate([
                    'from_id' => $record->registry_object_id,
                    'to_id' => $activity->registry_object_id,
                    'relation_type' => 'isOutputOf',
                    'relation_origin' => 'GRANTS'
                ]);
            }
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
            ->setLimit(0)
            ->get();

        return $relations;
    }

    /**
     * @param RegistryObject $record
     * @return array
     */
    public static function getImplicitRelationship(RegistryObject $record)
    {
        $provider = Connections::getImplicitProvider();

        // directly related
        $relations = $provider
            ->setFilter('from_key', $record->key)
            ->setLimit(0)
            ->get();
        return $relations;
    }

    public static function getReverseImplicitRelationship(RegistryObject $record
    ) {
        $provider = Connections::getImplicitProvider();

        // directly related
        $relations = $provider
            ->setFilter('to_key', $record->key)
            ->setLimit(0)
            ->setReverse(true)
            ->get();
        return $relations;
    }

    /**
     * @param RegistryObject $record
     * @return array
     */
    public static function getIdentifierRelationship(RegistryObject $record)
    {
        $provider = Connections::getIdentifierProvider();

        // directly related
        $relations = $provider
            ->setFilter('from_key', $record->key)
            ->setLimit(0)
            ->get();
        return $relations;
    }

    /**
     * Returns a list of directly related reverse relationships
     * from this record
     * includes reverse primary relationships
     *
     * @param RegistryObject $record
     * @return array
     */
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
     * A quick way to get affected IDs from a list of IDs
     * aim to replace getAffectedIDs function
     *
     * @param $ids
     * @return array
     */
    public static function getAffectedIDsFromIDs($ids)
    {
        $affectedIDs = [];
        $directAndReverse = [];

        // find directly affected
        $stdProvider = Connections::getStandardProvider();

        // directly related
        $direct = $stdProvider->init()
            ->setFilter('from_id', $ids)
            ->setLimit(0)
            ->get();

        foreach ($direct as $relation) {
            $affectedIDs[] = $relation->prop('to_id');
            $directAndReverse[] = $relation->prop('to_id');
        }

        // reverse
        $keys = RegistryObject::whereIn('registry_object_id', $ids)->get()->pluck('key')->toArray();
        $reverse = $stdProvider->init()
            ->setFilter('to_key', $keys)
            ->setLimit(0)
            ->get();

        foreach ($reverse as $relation) {
            $affectedIDs[] = $relation->prop('from_id');
            $directAndReverse[] = $relation->prop('from_id');
        }

        // funder
        $impProvider = Connections::getImplicitProvider();
        $funders = $impProvider->init()
            ->setFilter('from_id', $ids)
            ->setFilter('relation_type', 'isFundedBy')
            ->setLimit(0)
            ->get();

        foreach ($funders as $relation) {
            $affectedIDs[] = $relation->prop('from_id');
        }

        // parent collections
        $parentCollections = $impProvider->init()
            ->setFilter('from_id', $ids)
            ->setFilter('relation_type', 'isPartOf')
            ->setFilter('to_class', 'collection')
            ->setLimit(0)
            ->get();

        foreach ($parentCollections as $relation) {
            $affectedIDs[] = $relation->prop('to_id');
        }

        $idsAndImmediate = array_merge($ids, $directAndReverse);
        $keys = RegistryObject::whereIn('registry_object_id', $idsAndImmediate)->get()->pluck('key')->toArray();

        // child collections
        $childCollections = $impProvider->init()
            ->setFilter('to_key', $keys)
            ->setFilter('relation_type', 'isPartOf')
            ->setFilter('from_class', 'collection')
            ->setLimit(0)
            ->get();

        foreach ($childCollections as $relation) {
            $affectedIDs[] = $relation->prop('from_id');
        }

        //parent activities
        $parentActivities = $impProvider->init()
            ->setFilter('from_id', $ids)
            ->setFilter('relation_type', 'isPartOf')
            ->setFilter('to_class', 'activity')
            ->setLimit(0)
            ->get();

        foreach ($parentActivities as $relation) {
            $affectedIDs[] = $relation->prop('to_id');
        }

        // child activities
        $parentActivities = $impProvider->init()
            ->setFilter('to_id', array_merge($ids, $directAndReverse))
            ->setFilter('relation_type', 'isPartOf')
            ->setFilter('from_class', 'activity')
            ->setLimit(0)
            ->get();

        foreach ($parentActivities as $relation) {
            $affectedIDs[] = $relation->prop('from_id');
        }

        $affectedIDs = array_filter($affectedIDs, function($item) use ($ids){
            return !in_array($item, $ids);
        });
        $affectedIDs = array_values(array_unique($affectedIDs));

        return $affectedIDs;
    }

}