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
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->ro->id);

        $suggestor = new \ANDS\Registry\Suggestors\UserDataSuggestor();
        $suggestions = $suggestor->suggestByView($record);

        return $suggestions;
    }
}