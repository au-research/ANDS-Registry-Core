<?php
require_once(APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Related object Suggestor
 * @author Leo Monus <Leo.Monus@ands.org.au>
 * @author Richard Walker <Richard.Walker@ands.org.au>
 */
class Related_object_suggestor extends _GenericSuggestor {

    /**
     * Suggest Records based on related object ids, using Solr's mlt search.
     * Rely on Solr's score.
     * @return array suggested_records
     */
    function suggest() {

        //construct the query string
        $str = 'id:'.$this->ro->id;

        //call SOLR library
        $ci =& get_instance();
        $ci->load->library('solr');
        $ci->solr->init();
        $ci->solr
            ->init()
            ->setOpt('q', $str)
            ->setOpt('rows', '10')
            ->setOpt('fl', 'id,key,slug,title,score')
            ->setOpt('defType', 'edismax')
            ->setOpt('mlt', 'true')
            ->setOpt(
                'mlt.fl', 'related_party_one_id,'.
                'related_party_multi_id,related_activity_id,'.
                'related_service_id,related_collection_id')
            ->setOpt('mlt.count', '50');

        $suggestions = array();

        $result = $ci->solr->executeSearch(true);

        if ($result['moreLikeThis'][$this->ro->id]['numFound'] > 0) {
            foreach ($result['moreLikeThis'][$this->ro->id]['docs'] as $doc) {
                if (!in_array_r($doc, $suggestions)) {
                    $suggestions[] = $doc;
                }
            }
        }
        
        return $suggestions;
    }

    function __construct() {
        parent::__construct();
        set_exception_handler('json_exception_handler');
    }
}
