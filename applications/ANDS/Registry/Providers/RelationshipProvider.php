<?php


namespace ANDS\Registry\Providers;


use ANDS\RecordData;
use ANDS\Registry\Connections;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\RegistryObject\Identifier;
use ANDS\RegistryObject;
use ANDS\RegistryObject\ImplicitRelationship;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\RegistryObject\Relationship;
use ANDS\RegistryObject\IdentifierRelationship;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\Util\XMLUtil;
use ANDS\Registry\Providers\ORCID\ORCIDRecordsRepository;


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
            'identifier' => static::getIdentifierRelationship($record),
            'reverse_identifier' => static::getReverseIdentifierRelationship($record)
        ];

        return $allRelationships;
    }

    /**
     * @param RegistryObject $record
     * @return array
     */
    public static function getMergedRelationships(RegistryObject $record, $includeDuplicates = true)
    {
        $allRelationships = static::get($record);

        $result = [];
        foreach ($allRelationships as $type => $relations) {

            foreach ($relations as $key => $relation) {

                if (!array_key_exists($key, $result)) {
                    $result[$key] = $relation;
                    continue;
                }
                // exist, merge them
                $result[$key] = $result[$key]->mergeWith($relation->getProperties());
            }
        }

        if ($includeDuplicates != true) {
            return $result;
        }

        $duplicates = $record->getDuplicateRecords();

        if (count($duplicates) == 0) {
            return $result;
        }

        foreach ($duplicates as $duplicate) {
            $duplicateRelationships = static::get($duplicate);
            $allRelationships = collect($duplicateRelationships)
                ->flatten(1)->values()->all();
            foreach ($allRelationships as $relation) {

                $swappedRelation = $relation->switchFromRecord($record);
                $key = $swappedRelation->getUniqueID();

                if (array_key_exists($key, $result)) {
                    $result[$key]->mergeWith($swappedRelation->getProperties());
                } else {
                    unset($result[$key]);
                    $swappedKey = $swappedRelation->getUniqueID();
                    $result[$swappedKey] = $swappedRelation;
                }
            }
        }

        return $result;
    }

    /**
     * Returns if a record contains a specific relatedObject class
     *
     * @param RegistryObject $record
     * @param $class
     * @param array $processed
     * @return bool
     */
    public static function hasRelatedClass(RegistryObject $record, $class, $processed = [])
    {
        debug("Getting related class for $record->title($record->registry_object_id)");

        if (in_array($record->registry_object_id, $processed)) {
            return false;
        }

        // Explicit
        $explicitProvider = Connections::getStandardProvider();

        // direct
        $result = $explicitProvider->init()
            ->setFilter('to_class', $class)
            ->setFilter('from_id', $record->registry_object_id)
            ->count();

        if ($result > 0) {
            return true;
        }

        // reverse
        $result = $explicitProvider->init()
            ->setFilter('from_class', $class)
            ->setFilter('to_key', $record->key)
            ->count();

        if ($result > 0) {
            return true;
        }

        // direct implicit
        $implicitProvider = Connections::getImplicitProvider();

        $result = $implicitProvider->init()
            ->setFilter('to_class', $class)
            ->setFilter('from_id', $record->registry_object_id)
            ->count();

        if ($result > 0) {
            return true;
        }

        // reverse implicit
        $result = $implicitProvider->init()
            ->setFilter('from_class', $class)
            ->setFilter('to_id', $record->registry_object_id)
            ->count();

        if ($result > 0) {
            return true;
        }

        // duplicate
//        $duplicates = $record->getDuplicateRecords();
//        foreach ($duplicates as $duplicate) {
//            if (static::hasRelatedClass($duplicate, $class, $processed)) {
//                return true;
//            }
//            $processed[] = $duplicate->registry_object_id;
//        }

        return false;
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
    public static function createPrimaryLinks($record, $explicit_keys = []){

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
                if(trim($relation_type) == ''){
                    $relation_type = "hasAssociationWith";
                }
                foreach ($related_info->identifier as $i) {
                    $identifiers_div .= getResolvedLinkForIdentifier((string)$i['type'], trim((string)$i));
                    if ($related_info_title == '' and (string)$i['type'] == 'orcid' and trim((string)$i) != '' and $orcidRecord = ORCIDRecordsRepository::obtain((string)$i)) {
                        $related_info_title = $orcidRecord->full_name;
                    }
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

        // skip getting funder if this node is a party or a service
        if ($record->class == "party" || $record->class == "service") {
            return;
        }

        // find funder and saved it, getFunder is recursive by default
        if ($funder = $provider->getFunder($record)) {
            ImplicitRelationship::firstOrCreate([
                'from_id' => $record->registry_object_id,
                'to_id' => $funder->registry_object_id,
                'relation_type' => 'isFundedBy',
                'relation_origin' => 'GRANTS'
            ]);
        }

        // find all parents activities
        if ($activities = $provider->getParentsActivities($record)) {
            foreach ($activities as $activity) {

                $relationType = "isOutputOf";
                if ($record->class == "activity") {
                    $relationType = "isPartOf";
                }

                ImplicitRelationship::firstOrCreate([
                    'from_id' => $record->registry_object_id,
                    'to_id' => $activity->registry_object_id,
                    'relation_type' => $relationType,
                    'relation_origin' => 'GRANTS'
                ]);
            }
        }

        // skip getting parent collections if it's an activity
        if ($record->class == "activity") {
            return;
        }

        // find all parents collections
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
            ->setFilter('from_id', $record->registry_object_id)
            ->setLimit(0)
            ->get();

        return $relations;
    }

    /**
     * @param RegistryObject $record
     * @param bool $includeDuplicate
     * @return array
     */
    public static function getImplicitRelationship(RegistryObject $record)
    {
        $provider = Connections::getImplicitProvider();

        // directly related
        $relations = $provider
            ->setFilter('from_id', $record->registry_object_id)
            ->setLimit(0)
            ->get();

        return $relations;
    }

    /**
     * @param RegistryObject $record
     * @return array
     */
    public static function getReverseImplicitRelationship(RegistryObject $record)
    {
        $provider = Connections::getImplicitProvider();

        // use to_id here, because implicitProvider reads from implicit related objects
        // which has to_id instead of to_key
        $relations = $provider
            ->setFilter('to_id', $record->registry_object_id)
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
            ->setFilter('from_id', $record->registry_object_id)
            ->setLimit(0)
            ->get();

        return $relations;
    }

    /**
     * @param RegistryObject $record
     * @return array
     */
    public static function getReverseIdentifierRelationship(RegistryObject $record)
    {
        $provider = Connections::getIdentifierProvider();

        $identifiers = IdentifierProvider::get($record);

        if (count($identifiers) == 0) {
            return [];
        }

        $identifierValues = collect($identifiers)->pluck('value')
            ->unique()->toArray();

        // directly related
        $relations = $provider
//            ->setFilter('to_key', $record->key)
            ->setFilter('to_identifier', $identifierValues)
            ->setReverse(true)
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
            ->setLimit(0)
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
    public static function getAffectedIDsFromIDs($ids, $keys)
    {

        $affectedIDs = [];
        $directAndReverse = [];
        $directAndReverseKeys = [];
        $duplicateIDs = [];

        $ids = collect($ids)->map(function($item){
            return (int) $item;
        })->toArray();

        $duplicateRecords = self::getDuplicateRecordsFromIDs($ids);

        foreach($duplicateRecords as $record){
            $duplicateIDs[] = (int)$record->registry_object_id;

            $ids[] = (int)$record->registry_object_id;
            $keys[] = $record->key;

        }

        // find directly affected
        $stdProvider = Connections::getStandardProvider();

        // directly related
        $direct = $stdProvider->init()
            ->setFilter('from_id', $ids)
            ->setLimit(0)
            ->get();

        foreach ($direct as $relation) {
            $affectedIDs[] = (int)$relation->prop('to_id');
            $directAndReverse[] = (int)$relation->prop('to_id');
            $directAndReverseKeys[] = $relation->prop('to_key');
        }

        // reverse
        $reverse = $stdProvider->init()
            ->setFilter('to_key', $keys)
            ->setLimit(5000)
            ->get();

        foreach ($reverse as $relation) {
            $affectedIDs[] = (int)$relation->prop('from_id');
            $directAndReverse[] = (int)$relation->prop('from_id');
            $directAndReverseKeys[] = $relation->prop('from_key');
        }

        // funder
        $impProvider = Connections::getImplicitProvider();
        $funders = $impProvider->init()
            ->setFilter('from_id', array_merge($directAndReverse, $ids))
            ->setFilter('relation_type', 'isFundedBy')
            ->setLimit(0)
            ->get();

        foreach ($funders as $relation) {
            $affectedIDs[] = (int)$relation->prop('to_id');
        }

        // parent collections
        $parentCollections = $impProvider->init()
            ->setFilter('from_id', array_merge($directAndReverse, $ids))
            ->setFilter('relation_type', 'isPartOf')
            ->setFilter('to_class', 'collection')
            ->setLimit(0)
            ->get();

        foreach ($parentCollections as $relation) {
            $affectedIDs[] = (int)$relation->prop('to_id');
        }

        // child collections
        $childCollections = $impProvider->init()
            ->setFilter('to_id', array_merge($ids, $directAndReverse))
            ->setFilter('relation_type', ['isPartOf', 'isFundedBy', 'isOutputOf'])
            ->setFilter('from_class', 'collection')
            ->setFilter('to_class', 'collection')
            ->setLimit(0)
            ->get();

        foreach ($childCollections as $relation) {
            $affectedIDs[] = (int)$relation->prop('from_id');
        }

        //parent activities
        $parentActivities = $impProvider->init()
            ->setFilter('from_id', $ids)
            ->setFilter('relation_type', ['isPartOf', 'isOutputOf'])
            ->setFilter('to_class', 'activity')
            ->setLimit(0)
            ->get();

        foreach ($parentActivities as $relation) {
            $affectedIDs[] = (int) $relation->prop('to_id');
        }

        // reverse parent activities
        $reverseParentActivities = $impProvider->init()
            ->setFilter('to_key', $keys)
            ->setFilter('relation_type', ['hasPart', 'hasOutput', 'outputs'])
            ->setFilter('from_class', 'activity')
            ->setLimit(0)
            ->get();

        foreach ($reverseParentActivities as $relation) {
            $affectedIDs[] = (int)$relation->prop('from_id');
        }

        // child activities
        $parentActivities = $impProvider->init()
            ->setFilter('to_id', array_merge($ids, $directAndReverse))
            ->setFilter('relation_type', ['isPartOf', 'isFundedBy'])
            ->setFilter('from_class', 'activity')
            ->setLimit(0)
            ->get();

        foreach ($parentActivities as $relation) {
            $affectedIDs[] = (int)$relation->prop('from_id');
        }

        // identifier relationships
        $idenProvider = Connections::getIdentifierProvider();
        $relations = $idenProvider->init()
            ->setFilter('from_id', array_merge($ids, $directAndReverse))
            ->setLimit(0)
            ->get();

        foreach ($relations as $relation) {
            if ($relation->hasProperty('to_id')) {
                $affectedIDs[] = (int) $relation->prop('to_id');
            }
        }

        // Optimisation, convert $ids to list of identifiers
        $identifiers = Identifier::whereIn('registry_object_id', array_merge($ids, $directAndReverse))->pluck('identifier')->toArray();

        if (count($identifiers) > 0) {
            $reverseRelations = $idenProvider->init()
                ->setFilter('to_identifier', $identifiers)
                ->setLimit(0)
                ->get();

            foreach ($reverseRelations as $relation) {
                $affectedIDs[] = (int)$relation->prop('from_id');
            }
        }

        $affectedIDs = array_filter($affectedIDs, function($item) use ($ids){
            return $item && !in_array($item, $ids);
        });


        $affectedIDs = array_merge($duplicateIDs, $affectedIDs);

        $affectedIDs = collect($affectedIDs)
            ->flatten()->values()->unique()
            ->toArray();


        return $affectedIDs;
    }


    public static function getDuplicateRecordsFromIDs($ids)
    {

        $identifiers = Identifier::whereIn('registry_object_id', $ids)->get()->pluck('identifier')->unique()->toArray();

        $duplicateIDs = [];
        $recordIDs = Identifier::whereIn('identifier', $identifiers)->get()->pluck('registry_object_id')->unique()->filter(function($item) use ($ids){
            return !in_array((int)$item, $ids);
        })->toArray();

        $ids = array_merge($ids, $recordIDs);
        $duplicateIDs = array_merge($duplicateIDs, $recordIDs);
        while(count($recordIDs) > 0)
        {
            $moreIdentifiers = Identifier::whereIn('registry_object_id', $recordIDs)->get()->pluck('identifier')->unique()->filter(function($item) use ($identifiers){
                return (!in_array($item, $identifiers) && $item != "");
            })->unique()->toArray();

            if($moreIdentifiers){
                $recordIDs = Identifier::whereIn('identifier', $moreIdentifiers)->get()->pluck('registry_object_id')->unique()->filter(function($item) use ($ids){
                    return !in_array((int)$item, $ids);
                })->unique()->toArray();
                $ids = array_merge($ids, $recordIDs);
                $duplicateIDs = array_merge($duplicateIDs, $recordIDs);
            }else{
                $recordIDs = [];
            }

        }
        return RegistryObject::whereIn('registry_object_id', $duplicateIDs)
            ->where('status', 'PUBLISHED')
            ->get();
    }


    public static function getDuplicateRecordsFromIdentifiers($identifiers)
    {
        return Identifier::whereIn('identifier', $identifiers)->get()->pluck('registry_object_id')->unique()->toArray();
    }

}