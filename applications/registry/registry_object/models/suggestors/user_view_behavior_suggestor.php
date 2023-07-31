<?php

use ANDS\Cache\Cache;

require_once(REGISTRY_APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Temporal coverage Suggestor
 * Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class User_view_behavior_suggestor extends _GenericSuggestor
{
    public function suggest()
    {
        $id = $this->ro->id;

        $cacheKey = "ro.$id.user_view_behavior_suggestor";

        // getting suggestions from cache
        if ($suggestions = Cache::driver('suggestions')->get($cacheKey)) {
            return $suggestions;
        }

        // obtaining suggestions based on the record
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($id);
        $suggestor = new \ANDS\Registry\Suggestors\UserDataSuggestor();
        $suggestions = $suggestor->suggest($record);

        // cache for 1w (in minutes)
        $cacheDuration = 1440 * 7;

        // only cache if there's something to cache
        if (is_array($suggestions) && count($suggestions)) {
            Cache::driver('suggestions')->put($cacheKey, $suggestions, $cacheDuration);
        }

        return $suggestions;
    }
}