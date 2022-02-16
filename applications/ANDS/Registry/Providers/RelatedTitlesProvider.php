<?php


namespace ANDS\Registry\Providers;


use ANDS\Mycelium\RelationshipSearchService;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class RelatedTitlesProvider implements RIFCSProvider
{

    /**
     * @param RegistryObject $record
     */
    public static function process(RegistryObject $record)
    {
        // This method is not implemented
    }


    /**
     * @param RegistryObject $record
     */
    public static function get(RegistryObject $record)
    {
        // This method is not implemented
    }

    /**
     * Obtain an associative array for the indexable fields
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getIndexableArray(RegistryObject $record)
    {

        return [
            "related_party_one_title" => RelatedTitlesProvider::getRelatedPartyOneTitles($record),
            "related_party_multi_title" => RelatedTitlesProvider::getRelatedPartyMultiTitles($record),
            "related_activity_title" => RelatedTitlesProvider::getRelatedActivityTitles($record),
            "related_service_title" => RelatedTitlesProvider::getRelatedServiceTitles($record),
            // decided to drop related collections from the portal index
            // "related_collection_title" => RelatedTitlesProvider::getRelatedCollectionTitles($record),
        ];

    }


    /**
     * @param $record
     */
    private static function getRelatedPartyOneTitles($record){
        $search_params = ['from_id'=>$record->id, 'to_class' => 'party', 'not_to_type' => 'group'];
        return RelatedTitlesProvider::getSearchResult($search_params);
    }

    /**
     * @param $record
     * @throws \Exception
     */
    private static function getRelatedPartyMultiTitles($record){
        $search_params = ['from_id'=>$record->id, 'to_class' => 'party','to_type' => 'group'];
        return RelatedTitlesProvider::getSearchResult($search_params);
    }

    /**
     * @param $record
     * @throws \Exception
     */
    private static function getRelatedActivityTitles($record){
        $search_params = ['from_id'=>$record->id, 'to_class' => 'activity'];
        return RelatedTitlesProvider::getSearchResult($search_params);
    }

    /**
     * @param $record
     * @throws \Exception
     */
    private static function getRelatedServiceTitles($record){
        $search_params = ['from_id'=>$record->id, 'to_class' => 'service'];
        return RelatedTitlesProvider::getSearchResult($search_params);
    }

    /**
     * @param $record
     * @throws \Exception
     */
    private static function getRelatedCollectionTitles($record){
        $search_params = ['from_id'=>$record->id, 'to_class' => 'collection'];
        return RelatedTitlesProvider::getSearchResult($search_params);
    }


    private static function getSearchResult($search_params){
        $result = RelationshipSearchService::search($search_params);
        $result = $result->toArray();
        $related_titles = [];
        if(isset($result['contents']) && count($result['contents']) > 0 ){
            foreach($result['contents'] as $item){
                if(isset($item['to_title']) && !in_array($item['to_title'], $related_titles)){
                    $related_titles[] = $item['to_title'];
                }
            }
        }
        return array_unique($related_titles);
    }
}