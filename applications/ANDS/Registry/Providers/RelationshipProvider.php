<?php


namespace ANDS\Registry\Providers;


use ANDS\Mycelium\RelationshipSearchService;
use ANDS\RegistryObject;



/**
 * Class RelationshipProvider
 * @package ANDS\Registry\Providers
 */
class RelationshipProvider
{

    private static $max_count = 50;
    private static $batch_size = 400;
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
        // Processing is done via Mycelium
    }

    /**
     * Get All Relationships
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function get(RegistryObject $record, $limit=50)
    {
        $search_params =[];
        $search_params['from_id'] = $record->id;
        $search_params["rows"] = self::$batch_size;
        $search_params["start"] = 0;

        $allRelationships = [];

        do {
            $result = RelationshipSearchService::search($search_params);
            $result = $result->toArray();
            $result_count = $result['count'];
            $result_total = $result['total'];
            foreach($result['contents'] as $item){
                $allRelationships[] = $item;
                if(sizeof($allRelationships) >= $limit){
                    return $allRelationships;
                }
            }
            $search_params["start"] += self::$batch_size;
        } while ($result_count > 0 && $search_params["start"] <= $result_total);
        return $allRelationships;
    }

    /**
     * @param RegistryObject $record
     * @return array
     */
    public static function getMergedRelationships(RegistryObject $record, $includeDuplicates = true)
    {
        // duplicates are always included
        return static::get($record);
    }

    /**
     * Returns if a record contains a specific relatedObject class
     *
     * @param RegistryObject $record
     * @param $class
     * @return bool
     * @throws \Exception
     */
    public static function hasRelatedClass(RegistryObject $record, $class)
    {
        $search_params =[];
        $search_params['from_id'] = $record->id;
        $search_params['to_class'] = $class;

        $result = RelationshipSearchService::search($search_params);
        $result = $result->toArray();
        $result_total = $result['total'];

        return $result_total > 0;
    }

    /**
     * Returns  a record relationships by a specific relatedObject class
     *
     * @param RegistryObject $record
     * @param $class
     * @return array
     * @throws \Exception
     */
    public static function getRelatedObjectsByClassType(RegistryObject $record, $class, $type)
    {
        $search_params =[];
        $search_params['from_id'] = $record->id;
        if($type != null) $search_params['to_type'] = $type;
        if($class != null) $search_params['to_class'] = $class;
        $search_params["rows"] = self::$batch_size;
        $search_params["start"] = 0;
        $allRelationships = [];

        do {
            $result = RelationshipSearchService::search($search_params);
            $result = $result->toArray();
            $result_count = $result['count'];
            $result_total = $result['total'];
            foreach($result['contents'] as $item){
                $allRelationships[] = $item;
            }
            $search_params["start"] += self::$batch_size;
        } while ($result_count > 0 && $search_params["start"] <= $result_total);
        return $allRelationships;
    }

    public static function getRelationByType(RegistryObject $record, array $relations)
    {
        $search_params =[];
        $search_params['from_id'] = $record->id;
        $search_params['relation_type'] = $relations;
        $search_params["rows"] = self::$batch_size;
        $search_params["start"] = 0;

        $allRelationships = [];

        do {
            $result = RelationshipSearchService::search($search_params);
            $result = $result->toArray();
            $result_count = $result['count'];
            $result_total = $result['total'];
            foreach($result['contents'] as $item){
                $allRelationships[] = $item;
            }
            $search_params["start"] += self::$batch_size;
        } while ($result_count > 0 && $search_params["start"] <= $result_total);
        return $allRelationships;
    }

    /**
     * @param RegistryObject $record
     * @param $class
     * @param $relations
     * @return array
     * @throws \Exception
     *
     */
    public static function getRelationByClassAndType(RegistryObject $record, $class, $relations)
    {
        $search_params =[];
        $search_params['from_id'] = $record->id;
        $search_params['to_class'] = $class;
        if($relations != null){
            $search_params['relation_type'] = $relations;
        }
        $search_params["rows"] = self::$batch_size;
        $search_params["start"] = 0;

        $allRelationships = [];

        do {
            $result = RelationshipSearchService::search($search_params);
            $result = $result->toArray();
            $result_count = $result['count'];
            $result_total = $result['total'];
            foreach($result['contents'] as $item){
                $allRelationships[] = $item;
            }
            $search_params["start"] += self::$batch_size;
        } while ($result_count > 0 && $search_params["start"] <= $result_total);
        return $allRelationships;
    }

    /**
     * especially useful for finding publications, they are collections type="publication"
     * @param RegistryObject $record
     * @param $class
     * @param $relations
     * @return array
     * @throws \Exception
     *
     */
    public static function getRelationByClassTypeRelationType(RegistryObject $record, $class, $type, $relations)
    {
        $search_params =[];
        $search_params['from_id'] = $record->id;
        if($class != null){
            $search_params['to_class'] = $class;
        }
        if($type != null){
            $search_params['to_type'] = $type;
        }
        if($relations != null){
            $search_params['relation_type'] = $relations;
        }
        $search_params["rows"] = self::$batch_size;
        $search_params["start"] = 0;

        $allRelationships = [];

        do {
            $result = RelationshipSearchService::search($search_params);
            $result = $result->toArray();
            $result_count = $result['count'];
            $result_total = $result['total'];
            foreach($result['contents'] as $item){
                $allRelationships[] = $item;
            }
            $search_params["start"] += self::$batch_size;
        } while ($result_count > 0 && $search_params["start"] <= $result_total);
        return $allRelationships;
    }

    /*
     * Will return a list of  titles of any related party  who has a relationship that is  a funder
     * - both related objects and related info are returned
     */
    public static function getFunders($record)
    {
        $funders = [];
        $search_params = ['from_id'=>$record->id, 'to_class' => 'party', 'relation_type'=>'isFundedBy'];
        $result = RelationshipSearchService::search($search_params);
        $funderResult = $result->toArray();

        if(isset($funderResult['contents']) && count($funderResult['contents']) > 0 ){
            foreach($funderResult['contents'] as $party){
                if(array_key_exists('to_title', $party))
                $funders[] = $party['to_title'];
            }
        }
        return array_unique($funders);
    }

    /**
     * Returns the Related Data (relationship) to a given Identifier and Identifier Type
     *
     * @param $ro
     * @return array
     */
    public static function getRelatedDataToIdentifiers($identifier_value, $identifier_type) {

        $result = RelationshipSearchService::search([
            'to_identifier' => $identifier_value,
            'from_class' => 'collection',
            'to_identifier_type' => $identifier_type,
            'not_to_type' => 'software',
            'to_title' => '*'
        ], ['rows' => 5]);

        return $result->toArray();
    }

}