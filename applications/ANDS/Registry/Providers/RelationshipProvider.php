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
        $funder = GrantsConnectionsProvider::create()->getFunder($record);
        $record->setRegistryObjectMetadata('funder_id', $funder->registry_object_id);

        // TODO: save metadata parents_activity_ids
        // find directly related parents activity and all directly related parents activities of them until none are found


        // TODO: save metadata parents_collection_ids
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

        return [
            'funder' => $funder,
            'parents_activities' => null,
            'parents_collections' => null
        ];
    }
}