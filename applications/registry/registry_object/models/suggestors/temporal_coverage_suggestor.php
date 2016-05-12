<?php
require_once(REGISTRY_APP_PATH. 'registry_object/models/_GenericSuggestor.php');

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
    private $minYear = 9999999;
    private $maxYear = 0;

    function suggest() {

        //construct the query string
        $str = 'id:'.$this->ro['registry_object_id'];
        $this->processTemporal();
		$earliest = '*';
        $latest = '*';
        if($this->getEarliestAsYear() != 9999999)
            $earliest = $this->getEarliestAsYear();
        if($this->getLatestAsYear() != 0)
            $latest = $this->getLatestAsYear();

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
                ->setOpt('fq', '-id:'.$this->ro['registry_object_id'])
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

    function processTemporal()
    {
        $this->minYear = 9999999;
        $this->maxYear = 0;
        $temporalArray = array();
        $sxml = simplexml_load_string($this->ro['data'], 'SimpleXMLElement', LIBXML_NOENT);
        $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
        $temporals = $sxml->xpath('//ro:temporal/ro:date');
        foreach ($temporals AS $temporal) {
            $type = (string)$temporal["type"];
            $value = $this->getWTCdate((string)$temporal);
            if ($value)
                $temporalArray[] = array('type' => $type, 'value' => $value);
        }
        return $temporalArray;
    }

    function getEarliestAsYear()
    {
        //TODO: write the function :-)
        return $this->minYear;
    }

    function getLatestAsYear()
    {
        //TODO: write the function :-)
        return $this->maxYear;
    }

    function getWTCdate($value)
    {
        utc_timezone();
        // "Year and only year" (i.e. 1960) will be treated as HH SS by default
        if (strlen($value) == 4) {
            // Assume this is a year:
            $value = "Jan 1 " . $value;
        } else if (preg_match("/\d{4}\-\d{2}/", $value) === 1) {
            $value = $value . "-01";
        }

        if (($timestamp = strtotime($value)) === false) {
            return false;
        } else {
            $date = getDate($timestamp);
            if ($date['year'] > $this->maxYear)
                $this->maxYear = $date['year'];
            if ($date['year'] < $this->minYear)
                $this->minYear = $date['year'];
            return date('Y-m-d\TH:i:s\Z', $timestamp);
        }

        reset_timezone();
    }

    function __construct() {
        parent::__construct();
        set_exception_handler('json_exception_handler');
    }
}
