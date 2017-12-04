<?php

use ANDS\Repository\RegistryObjectsRepository;

require_once(REGISTRY_APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Subjects Suggestor
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @author Leo Monus <Leo.Monus@ands.org.au>
 * @author Richard Walker <Richard.Walker@ands.org.au>
 */
class Subjects_suggestor extends _GenericSuggestor {

    /**
     * Suggest Records based on subject_value_unresolved value
     * from the local SOLR core.
     * Rely on Solr's score.
     *
     * @return array suggested_records
     */
    function suggest() {
        // CC-2068. Updated Subject Suggestions
        $record = RegistryObjectsRepository::getRecordByID($this->ro->id);
        $suggestor = new \ANDS\Registry\Suggestors\SubjectSuggestor();
        $suggestions = $suggestor->suggest($record);
        return $suggestions;
    }

    function __construct() {
        parent::__construct();
        set_exception_handler('json_exception_handler');
    }
}
