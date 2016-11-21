<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

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
        // TODO: translate $ro->addRelationship() to here
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
        $funder = $provider->getFunder($record);
        $record->setRegistryObjectMetadata('funder_id', $funder->registry_object_id);

        // find all parents activities
        $activities = $provider->getParentsActivities($record);
        $record->setRegistryObjectMetadata(
            'parents_activity_ids',
            implode(',', collect($activities)->pluck('registry_object_id')->toArray())
        );

        // find all parents collections
        $collections = $provider->getParentsCollections($record);
        $record->setRegistryObjectMetadata(
            'parents_collection_ids',
            implode(',', collect($collections)->pluck('registry_object_id')->toArray())
        );
    }

    /**
     * Returns a list of directly related relationships
     * from this record
     *
     * @return array
     */
    public static function getDirectRelationship()
    {
        // TODO: use Connections Provider to get these data
        return [];
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
        $funderID = $record->getRegistryObjectMetadata('funder_id')->value;
        $funder = RegistryObjectsRepository::getRecordByID($funderID);

        // parents_activities
        $parentsActivityIDs = $record->getRegistryObjectMetadata('parents_activity_ids')->value;
        $parentsActivities = RegistryObject::whereIn('registry_object_id', explode(',', $parentsActivityIDs))->get()->toArray();

        // parents_activities
        $parentsCollectionIDs = $record->getRegistryObjectMetadata('parents_collection_ids')->value;
        $parentsCollections = RegistryObject::whereIn('registry_object_id', explode(',', $parentsCollectionIDs))->get()->toArray();

        return [
            'funder' => $funder,
            'parents_activities' => $parentsActivities,
            'parents_collections' => $parentsCollections
        ];
    }
}