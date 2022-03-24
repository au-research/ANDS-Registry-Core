<?php
require_once(REGISTRY_APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Related object Suggestor
 * @author Leo Monus <Leo.Monus@ands.org.au>
 * @author Richard Walker <Richard.Walker@ands.org.au>
 */
class Related_object_suggestor extends _GenericSuggestor
{

    public function suggest()
    {
        $id = $this->ro->id;

        // obtaining suggestions based on the record
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($id);
        $suggestor = new \ANDS\Registry\Suggestors\RelatedDatasetSuggestor();
        $suggestions =  $suggestor->suggest($record);

        return $suggestions;
    }

    public function __construct()
    {
        parent::__construct();
        set_exception_handler('json_exception_handler');
    }
}
