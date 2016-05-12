<?php
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
     * @return array suggested_records
     */
    function suggest() {

        //Get subjects from the XML
        $suggestions = array();
        $sxml = simplexml_load_string($this->ro['data'], 'SimpleXMLElement', LIBXML_NOENT);
        // Subject matches
        $my_subjects = array();
        if ($sxml->registryObject->{strtolower($this->ro_class)}->subject) {
            foreach ($sxml->registryObject->{strtolower($this->ro_class)}->subject as $subject) {
                $my_subjects[] = (string) removeBadValue($subject);
            }
        }

        //construct the query stirng
        $str = '';
        foreach($my_subjects as $s) {
            $str.='subject_value_unresolved:('.$s.') ';
        }
        if($str != '')
        {
            //call SOLR library
            $maxRows = 50;
            $ci =& get_instance();
            $ci->load->library('solr');
            $ci->solr->init();
            $ci->solr
                ->init()
                ->setOpt('q', $str)
                ->setOpt('rows', $maxRows)
                ->setOpt('fl', 'id,key,slug,title,score')
                ->setOpt('fq', '-id:'.$this->ro['registry_object_id'])
                ->setOpt('fq', 'class:collection')
                ->setOpt('defType', 'edismax');

            $result = $ci->solr->executeSearch(true);
            if($result['response']['numFound'] > 0) {

                $maxScore = floatval($result['response']['maxScore']);
                $intScore = 0;
                foreach($result['response']['docs'] as $doc) {
                    $doc['score'] = ($doc['score'] / $maxScore) * (1-($intScore/$maxRows));
                    $intScore++;
                    if (is_array($doc['slug'])) $doc['slug'] = $doc['slug'][0];
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
