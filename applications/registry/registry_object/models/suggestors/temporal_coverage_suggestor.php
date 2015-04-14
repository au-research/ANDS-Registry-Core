<?php
require_once(APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Temporal coverage Suggestor
 * @author Leo Monus <Leo.Monus@ands.org.au>
 * @author Richard Walker <Richard.Walker@ands.org.au>
 */
class Temporal_coverage_suggestor extends _GenericSuggestor {
	
    /**
     * Suggest Records based on related object ids, using the Solr's mlt search.
     * Rely on Solr's score.
     * @return array suggested_records
     */
    function suggest() {

        //construct the query string
        $str = 'id:'.$this->ro->id;
        $this->ro->processTemporal();
		$earliest = '*';
        $latest = '*';
        if($this->ro->getEarliestAsYear() != 9999999)
            $earliest = $this->ro->getEarliestAsYear();
        if($this->ro->getLatestAsYear() != 0)
            $latest = $this->ro->getLatestAsYear();

        //call SOLR library
        $suggestions = array();
        $this->minYear = 9999999;
        $this->maxYear = 0;
        $maxRows = 50;
        if($earliest != '*' || $latest != '*')
        {
            $str = 'date_from:['.$earliest.'-01-01T00:00:00Z TO '.$latest.'-12-31T23:59:59Z] AND date_to:['.$earliest.'-01-01T00:00:00Z TO '.$latest.'-12-31T23:59:59Z]';
            $ci =& get_instance();
            $ci->load->library('solr');
            $ci->solr->init();
            $ci->solr
                ->init()
                ->setOpt('q', $str)
                ->setOpt('rows', '50')
                ->setOpt('fq', '-id:'.$this->ro->id)
                ->setOpt('fq', 'class:collection')
                ->setOpt('fl', 'id,key,slug,title,score');

            $result = $ci->solr->executeSearch(true);
            if($result['response']['numFound'] > 0) {
                $maxScore = floatval($result['response']['maxScore']);
                $intScore = 0;
                foreach($result['response']['docs'] as $doc) {
                    $doc['score'] = ($doc['score'] / $maxScore) * (1-($intScore/$maxRows));
                    $intScore++;
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
