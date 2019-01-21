<?php
require_once(REGISTRY_APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Temporal coverage Suggestor
 * Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class User_view_behavior_suggestor extends _GenericSuggestor
{
    public function suggest()
    {
        $id = $this->ro->id;

        $cacheKey = "ro.$id.user_view_behavior_suggestor";

        // cache for 1w
        $cacheDuration = 60 * 1440 * 7;

        return \ANDS\Cache\Cache::driver('suggestions')->remember($cacheKey, $cacheDuration, function() use ($id){
            $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($id);
            $suggestor = new \ANDS\Registry\Suggestors\UserDataSuggestor();
            $suggestions = $suggestor->suggest($record);
            return $suggestions;
        });

    }
}