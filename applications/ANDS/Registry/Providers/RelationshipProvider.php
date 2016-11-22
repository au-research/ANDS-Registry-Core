<?php


namespace ANDS\Registry\Providers;


use ANDS\Registry\Connections;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\RegistryObject\Relationship;


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
        deleteAllRelationshipsFromId($record->registry_object_id);


        // TODO: translate $ro->addRelationship() to here
    }

    public static function deleteAllRelationshipsFromId($registry_object_id){
        RegistryObjectsRepository::deleteIdentifierRelationships($registry_object_id);
        RegistryObjectsRepository::deleteIdentifierRelationships($registry_object_id);
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
        $record->setRegistryObjectMetadata('funder_id', null);
        $record->setRegistryObjectMetadata('parents_collection_ids', null);
        $record->setRegistryObjectMetadata('parents_activity_ids', null);

        // find funder and saved it, getFunder is recursive by default
        if ($funder = $provider->getFunder($record)) {
            $record->setRegistryObjectMetadata('funder_id', $funder->registry_object_id);
        }

        // find all parents collections
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