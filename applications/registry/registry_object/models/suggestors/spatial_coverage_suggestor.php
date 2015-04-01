<?php
require_once(APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Temporal coverage Suggestor
 * @author Leo Monus <Leo.Monus@ands.org.au>
 * @author Richard Walker <Richard.Walker@ands.org.au>
 */
class Spatial_coverage_suggestor extends _GenericSuggestor {

    /**
     * Suggest Records based on related object ids, using the Solr's mlt search.
     * Rely on Solr's score.
     * @return array suggested_records
     */
    function suggest() {

        //construct the query string
        $ci =& get_instance();
        $ci->load->library('solr');
        $ci->solr->init();
        $str = 'id:'.$this->ro->id;
        $centers = array();
        $suggestions = array();
        $ci->solr
            ->init()
            ->setOpt('q', $str)
            ->setOpt('rows', '50')
            ->setOpt('fl', 'spatial_coverage_centres');
        $result = $ci->solr->executeSearch(true);
        if($result['response']['numFound'] > 0) {
            foreach($result['response']['docs'] as $doc) {
                if(isset($doc['spatial_coverage_centres']))
                    $centers[] = $doc['spatial_coverage_centres'];
            }
        }
        foreach($centers as $key=>$center)
        {
            $maxRows = 50;
            $latLon = explode(' ', $center[0]);
            if(!is_array($latLon) || count($latLon) < 2)
                $latLon = explode(',', $center[0]);
            if(is_array($latLon) && count($latLon == 2)){
                $ci->solr
                    ->init()
                    ->setOpt('q', '*:*')
                    ->setOpt('rows', $maxRows)
                    ->setOpt('fq', '-id:'.$this->ro->id)
                    ->setOpt('fq', 'class:collection')
                    ->setOpt('fq', '{!geofilt pt='.$latLon[1].','.$latLon[0].' sfield=spatial_coverage_extents d=50}')
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
        }

        return $suggestions;
    }

    function __construct() {
        parent::__construct();
        set_exception_handler('json_exception_handler');
    }
}