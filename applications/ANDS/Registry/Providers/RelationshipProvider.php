<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;

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
        // TODO: save metadata funder_id
        // TODO: save metadata parents_activity_ids
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
     * @return array
     */
    public static function getGrantsRelationship()
    {
        // TODO: read from saved metadata and generate RegistryObject records
        return [
            'funder' => null,
            'parents_activities' => null,
            'parents_collections' => null
        ];
    }
}