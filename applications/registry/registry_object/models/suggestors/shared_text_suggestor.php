<?php
require_once(APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Shared text Suggestor
 * @author Leo Monus <Leo.Monus@ands.org.au>
 * @author Richard Walker <Richard.Walker@ands.org.au>
 */
class Shared_text_suggestor extends _GenericSuggestor {
	
    /**
     * Suggest Records based on the title or description
     * containing similar text, using the Solr's mlt search.
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
            ->setOpt('rows', '1')
            ->setOpt('fl', 'id,key,slug,title,score,class')
            ->setOpt('defType', 'edismax')
            ->setOpt('fq', 'class:collection')
            ->setOpt('mlt', 'true')
            ->setOpt('mlt.fl', 'description,display_title')
            ->setOpt('mlt.count', '100');
        
        $suggestions = array();

        $result = $ci->solr->executeSearch(true);

        if($result['moreLikeThis'][$this->ro->id]['numFound'] > 0) {
            $maxScore = floatval($result['moreLikeThis'][$this->ro->id]['maxScore']);
            foreach($result['moreLikeThis'][$this->ro->id]['docs'] as $doc) {
                if($doc['class'] == 'collection'){
                    $doc['score'] = $doc['score'] / $maxScore;
                    $doc['RDAUrl'] = portal_url($doc['slug'].'/'.$doc['id']);
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
