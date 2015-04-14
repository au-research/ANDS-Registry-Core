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
        $maxRows = 50;
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
            ->setOpt('mlt.fl', 'description_value,title_search')
            ->setOpt('mlt.count', $maxRows);
        
        $suggestions = array();

        $result = $ci->solr->executeSearch(true);

        if(isset($result['moreLikeThis'][$this->ro->id]) &&  $result['moreLikeThis'][$this->ro->id]['numFound'] > 0) {
            $maxScore = false;
            $intScore = 0;
            foreach($result['moreLikeThis'][$this->ro->id]['docs'] as $doc) {
                if($doc['class'] == 'collection'){
                    if(!$maxScore)
                        $maxScore = floatval($doc['score']);
                    $doc['score'] = $doc['score'] / $maxScore * (1-($intScore/$maxRows));
                    $intScore++;;
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
