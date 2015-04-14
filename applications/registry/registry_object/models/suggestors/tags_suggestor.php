<?php
require_once(APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Subjects Suggestor
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @author Leo Monus <Leo.Monus@ands.org.au>
 * @author Richard Walker <Richard.Walker@ands.org.au>
 */
class Tags_suggestor extends _GenericSuggestor {

    /**
     * Suggest Records based on public tags value
     * @return array suggested_records
     */
    function suggest() {

        $ci =& get_instance();

        $ci->db->select('tag')
            ->from('registry_object_tags')
            ->where('key',$this->ro->key);

        $query = $ci->db->get();
        $str = '';
        $suggestions = array();
        //construct the query stirng
        if($query->result_array())
        foreach($query->result_array() AS $row){
            $str.='tag_search:('.$row['tag'].') ';
        }
//return(array($str));
        //call SOLR library
        if($str != '')
        {
            $maxRows = 50;
            $ci->load->library('solr');
            $ci->solr->init();
            $ci->solr
                ->init()
                ->setOpt('q', $str)
                ->setOpt('rows', $maxRows)
                ->setOpt('fl', 'id,key,slug,title,score')
                ->setOpt('fq', '-id:'.$this->ro->id)
                ->setOpt('fq', 'class:collection')
                ->setOpt('defType', 'edismax');

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
