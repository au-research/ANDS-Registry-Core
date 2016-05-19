<?php
namespace ANDS\API\Registry\Handler;

use \Exception as Exception;

/**
 * Handles registry/grants
 * getGrants API
 *
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class ActivitiesHandlerV2 extends Handler
{

    private $validActivitiesTypes = ['grant', 'program', 'project', 'award', 'dataset'];
    private $defaultFlags = "id,title,list_title,display_title,alt_display_title,alt_list_title,key,activity_status,type,identifier_type,identifier_value,description,subject_type,subject_value_resolved,identifier_type,identifier_value,funding_amount,funding_scheme,earliest_year,latest_year,record_created_timestamp,record_modified_timestamp,funders,administering_institution,researchers,principal_investigator";

    /**
     * Handling the grants method
     *
     * @return array
     * @throws Exception
     */
    public function handle()
    {
        //load libraries for use
        $this->ci->load->library('solr');
        $this->ci->load->model('registry/registry_object/registry_objects',
            'ro');

        /**
         * valid entry points
         * /activities
         * /activities/{type}/
         * /activities/{type}/{id}/{subtype}
         */
        if ($this->params['identifier']) {

            // /activities/{type}
            $type = $this->params['identifier'];

            //remove s
            if (substr($type, -1) == 's'){
                $type = substr($type, 0, -1);
            }

            if ($this->params['object_module']) {
                // activities/{type}/{id}
                if ($this->params['object_submodule']) {
                    // activities/{type}/{id}/{subtype}
                    $subtype = $this->params['object_submodule'];
                    if (substr($subtype, -1) == 's'){
                        $subtype = substr($subtype, 0, -1);
                    }

                    //get the funder name using ID
                    $funderID = $this->params['object_module'];
                    $this->ci->solr->init()->setCore('portal')
                        ->setOpt('rows', 1)
                        ->setOpt('fl', '*')
                        ->setOpt('fq', '+id:'.$funderID);
                    $funderResult = $this->ci->solr->executeSearch(true);
                    if ($funderResult['response']['numFound'] > 0) {
                        $funderName = $funderResult['response']['docs'][0]['title'];
                        if ($funderName) {
                            return $this->search(['type' => $subtype, 'funder' => '"'.$funderName.'"']);
                        } else {
                            throw new Exception("No Funder with ID ". $funderID. " found!");
                        }
                    } else {
                        throw new Exception("No Funder with ID ". $funderID. " found!");
                    }

                } else {
                    // activities/{type}/{id}
                    $id = $this->params['object_module'];
                    return $this->lookup($id, $type);
                }

            } else {
                // activities/{type}
                if (in_array($type, $this->validActivitiesTypes)) {
                    return $this->search(['type' => $type]);
                } elseif ($type == 'funder') {
                    return $this->browseFunders();
                } else {
                    throw new Exception($type. " not implemented yet");
                }
            }

        } else {
            // activities/
            return $this->search();
        }

        //shouldn't get here
        throw new Exception("Unexpected Error");
    }

    private function browseFunders(){
        $this->ci->load->library('solr');
        $this->ci->solr
            ->init()
            ->setCore('portal')
            ->setOpt('rows', 0)
            ->setFacetOpt('field', 'funders');
        $result = $this->ci->solr->executeSearch(true);

        $funders = array();

        //todo error checking
        $funderResult = $result['facet_counts']['facet_fields']['funders'];
        for ($i = 0; $i < sizeof($funderResult)-1; $i+=2) {
            $funderName = $funderResult[$i];
            $this->ci->solr->init()->setCore('portal')
                ->setOpt('rows', 1)
                ->setOpt('fl', 'id,title,key,type')
                ->setOpt('fq', '+class:party')
                ->setOpt('fq', '+type:group')
                ->setOpt('fq', 'title:"'.$funderName.'"');
            $thisFunderResult = $this->ci->solr->executeSearch(true);

            if ($thisFunderResult['response']['numFound'] > 0) {
                $thisFunderResult = $thisFunderResult['response']['docs'][0];

                $thisFunderResult['links'] = [
                    'rel' => "self",
                    'href' => $this->getHateOASLink($thisFunderResult, "self")
                ];

                //todo funder formating
                $funders[] = $thisFunderResult;
            } else {
                $funders[] = $funderName;
            }

        }

        return $funders;
    }

    private function lookup($id, $type) {
        $this->ci->load->library('solr');
        $this->ci->solr->setOpt('fq', '+id:'.$id);

        $params = $this->ci->input->get();

        //flags setup
        $fl = (isset($params['fl'])) ? $params['fl'] : $this->defaultFlags;
        $this->ci->solr->setOpt('fl', $fl);
        $result = $this->ci->solr->executeSearch(true);

        //todo error checking
        $record = $result['response']['docs'][0];

        if ($type === "funder") {

            //get type facet for counts
            $this->ci->solr
                ->init()
                ->setCore('portal')
                ->setOpt('fq', '+funders:"'.$record['title'].'"')
                ->setOpt('rows', 0)
                ->setFacetOpt('field', 'type')
                ->setFacetOpt('mincount', '1');
            $funderResult = $this->ci->solr->executeSearch(true);
            $funderResultFacet = $funderResult['facet_counts']['facet_fields']['type'];

            $typeFacets = [];
            for ($i = 0; $i < sizeof($funderResultFacet) - 1; $i += 2) {
                $typeFacets[] = [
                    'type' => $funderResultFacet[$i],
                    'count' => $funderResultFacet[$i + 1]
                ];
            }

            $record['counts'] = $typeFacets;
        }


        return $this->formatRecord($record, $flags = explode(',', $fl));
    }

    private function search($defaultParams = array())
    {
        $this->ci->load->library('solr');
        $this->ci->solr->init()->setCore('portal');

        $gets = $this->ci->input->get() ? $this->ci->input->get() : [];
        $params = array_merge($gets, $defaultParams);

        // q
        if ($q = (isset($params['q'])) ? $params['q'] : null) {
            $this->ci->solr->setOpt('q', $this->canbeFuzzy($q));
            $this->ci->solr->setOpt('defType', 'edismax');
            $this->ci->solr->setOpt('qf', '_text_ identifier_value title title_search subject_value_resolved subject_value_search identifier_value_search researchers researchers_search principal_investitagor');
        }

        //Only search for activity
        $this->ci->solr->setOpt('fq', '+class:"activity"');

        // type
        if ($type = (isset($params['type'])) ? $params['type'] : null) {
            $this->ci->solr->setOpt('fq', '+type:("' . $type . '")');
        }

        //funder
        if ($funder = (isset($params['funder'])) ? $params['funder'] : null) {
            $this->ci->solr->setOpt('fq', '+funders_search:(' . $funder . ')');
        }

        //subject
        if ($subject = (isset($params['subject'])) ? $params['subject'] : null) {
            $this->ci->solr->setOpt('fq',
                '+subject_value_resolved_search:(' . $subject . ')');
        }

        //fundingScheme
        if ($fundingScheme = (isset($params['fundingScheme'])) ? $params['fundingScheme'] : null) {
            $this->ci->solr->setOpt('fq',
                '+funding_scheme_search:(' . $fundingScheme . ')');
        }

        //purl
        if ($purl = (isset($params['purl'])) ? $params['purl'] : null) {
            $this->ci->solr->setOpt('fq',
                '+identifier_value:("' . $purl . '")');
        }

        //identifier
        $identifier = (isset($params['identifier'])) ? $params['identifier'] : null;
        if ($identifier) {
            $identifier = $this->canbeFuzzy($identifier);
            $this->ci->solr->setOpt('fq',
                '+(identifier_value:(' . $identifier . ') OR identifier_value_search:('.$identifier.'))');
        }

        //individual id
        $id = (isset($params['id'])) ? $params['id'] : null;
        if ($id) {
            $this->ci->solr->setOpt('fq',
                '+identifier_value:*' . urldecode($id) . '*');
        }

        //title
        $title = (isset($params['title'])) ? $params['title'] : null;
        if ($title) {
            $this->ci->solr->setOpt('fq', 'title_search:(' . $title . ')');
        }

        //institution
        if ($institutions = (isset($params['institution'])) ? $params['institution'] : null) {
            $this->ci->solr->setOpt('fq',
                '+administering_institution_search:"' . $institutions . '"');
        }

        //description
        if ($descriptions = (isset($params['description'])) ? $params['description'] : null) {
            $descriptions = $this->canbeFuzzy($descriptions);
            $this->ci->solr->setOpt('fq',
                '+description:(' . $descriptions . ')');
        }

        //principalInvestigator
        if ($principalInvestigator = (isset($params['principalInvestigator'])) ? $params['principalInvestigator'] : null) {
            $this->ci->solr->setOpt('fq',
                '+principal_investigator_search:"' . $principalInvestigator . '"');
        }

        //researcher
        if ($researcher = (isset($params['researcher'])) ? $params['researcher'] : null) {
            $researcher = $this->canbeFuzzy($researcher);
            $this->ci->solr->setOpt('fq',
                '+researchers_search:"' . $researcher . '"');
        }

        //status
        if ($status = (isset($params['status'])) ? $params['status'] : null) {
            $this->ci->solr->setOpt('fq', '+activity_status:(' . $status . ')');
        }

        //addedSince
        if ($addedSince = (isset($params['addedSince'])) ? $params['addedSince'] : null) {
            //convert to SOLR timestamp
            $addedSince = date('c', strtotime($addedSince)) . 'Z';
            $this->ci->solr->setOpt('fq',
                '+record_created_timestamp:[' . $addedSince . ' TO *]');
        }

        //modifiedSince
        if ($modifiedSince = (isset($params['modifiedSince'])) ? $params['modifiedSince'] : null) {
            //convert to SOLR timestamp
            $modifiedSince = date('c', strtotime($modifiedSince)) . 'Z';
            $this->ci->solr->setOpt('fq',
                '+record_modified_timestamp:[' . $modifiedSince . ' TO *]');
        }

        //limit
        //default to 30
        $limit = (isset($params['limit'])) ? $params['limit'] : 30;
        $this->ci->solr->setOpt('rows', $limit);

        // offset, default to 0
        $offset = (isset($params['offset'])) ? $params['offset'] : 0;
        $this->ci->solr->setOpt('offset', $offset);

        // facet setup
        $this->ci->solr->setFacetOpt('mincount', 1);
        if ($this->ci->input->get('facets')) {
            $facets = explode(',', $this->ci->input->get('facets'));
            foreach ($facets as $facet) {
                switch ($facet) {
                    case "institutions":
                        $this->ci->solr->setFacetOpt('field',
                            'administering_institution');
                        break;
                    case "funders":
                        $this->ci->solr->setFacetOpt('field', 'funders');
                        break;
                    case "type":
                        $this->ci->solr->setFacetOpt('field', 'type');
                        break;
                    case "fundingScheme":
                        $this->ci->solr->setFacetOpt('field', 'funding_scheme');
                        break;
                }
            }
        }

        //flags setup
        $fl = (isset($params['fl'])) ? $params['fl'] : $this->defaultFlags;
        $this->ci->solr->setOpt('fl', $fl);

        //execute search and store the result
        $result = $this->ci->solr->executeSearch(true);

        if (array_key_exists('error', $result)) {
            throw new Exception($result['error']['msg']);
        }

        $solrURL = $this->ci->solr->getSolrUrl().'select?'.$this->ci->solr->constructFieldString();

        //clean up facet
        $facets = array();

        foreach ($result['facet_counts']['facet_fields'] as $facetField => $facetValues) {
            $facets[$facetField] = array();
            for ($i = 0; $i < sizeof($facetValues) - 1; $i += 2) {
                $facets[$facetField][] = [
                    'key' => $facetValues[$i],
                    'value' => $facetValues[$i + 1]
                ];
            }
        }

        // format the records
        $records = $result['response']['docs'];

        foreach ($records as &$record) {
            $record = $this->formatRecord($record, $flags = explode(',', $fl));
        }

        //response setup
        $response = array(
            'numFound' => $result['response']['numFound'],
            'offset' => $offset,
            'limit' => (int) $limit,
            'records' => $records
        );

        if ($this->ci->input->get('facets')) {
            $response['facets'] = $facets;
        }

        if ($debug = isset($params['debug']) ? true : false) {
            $response['solrURL'] = $solrURL;
        }

        // HATEOAS
        $response['links'] = [];
        foreach ($this->validActivitiesTypes as $type) {
            $link = [
                'rel' => $type,
                'href' => $this->getHateOASLink(null, $type)
            ];
            $response['links'][] = $link;
        }
        $response['links'][] = [
            'rel' => 'funder',
            'href' => $this->getHateOASLink(null, 'funder')
        ];

        return $response;
    }

    private function formatRecord($record, $flags){

        // fix values
        foreach ($flags as $f) {
            if (!array_key_exists($f, $record)) {
                $record[$f] = null;
            }
        }

        //fix activity_status
        $record = $this->changeKey($record, 'activity_status', 'status');
        $record = $this->changeKey($record, 'funding_amount',
            'fundingAmount');
        $record = $this->changeKey($record, 'funding_scheme',
            'fundingScheme');
        $record = $this->changeKey($record, 'earliest_year', 'startDate');
        $record = $this->changeKey($record, 'latest_year', 'endDate');
        $record = $this->changeKey($record, 'record_modified_timestamp',
            'dateTimeModified');
        $record = $this->changeKey($record, 'record_created_timestamp',
            'dateTimeCreated');
        $record = $this->changeKey($record, 'funders', 'funder');
        $record = $this->changeKey($record, 'administering_institution',
            'institutions');
        $record = $this->changeKey($record, 'principal_investigator',
            'principalInvestigator');

        //fix identifiers & purl
        $identifiers = [];
        $record['purl'] = null;
        if (array_key_exists('identifier_type',
                $record) && $record['identifier_type']
        ) {
            foreach ($record['identifier_type'] as $key => $idType) {
                if (array_key_exists('identifier_value', $record) &&
                    array_key_exists($key, $record['identifier_value'])
                ) {
                    $identifiers[] = $record['identifier_value'][$key];
                }

                if (strtolower(trim($idType)) === "purl" &&
                    array_key_exists($key, $record['identifier_value'])
                ) {
                    $record['purl'] = $record['identifier_value'][$key];
                }
            }
            unset($record['identifier_type']);
            unset($record['identifier_value']);
        }
        $record['identifiers'] = sizeof($identifiers) > 0 ? $identifiers : null;

        //fix subjects
        $subjects = [];
        if (array_key_exists('subject_type',
                $record) && $record['subject_type']
        ) {
            foreach ($record['subject_type'] as $key => $idType) {
                if (array_key_exists('subject_value_resolved', $record) &&
                    array_key_exists($key,
                        $record['subject_value_resolved'])
                ) {
                    $subjects[] = $record['subject_value_resolved'][$key];
                }
            }
            unset($record['subject_type']);
            unset($record['subject_value_resolved']);
        }
        $record['subjects'] = sizeof($subjects) > 0 ? $subjects : null;

        // Flags determine the additional information we would want
        // that is not covered in the default response
        // mainly use for testing

        $flags = $this->ci->input->get('flags') ? $this->ci->input->get('flags') : false;
        if ($flags && $flags = explode(',', $flags)) {
            foreach ($flags as $flag) {
                switch ($flag) {
                    case "titles":
                        $titles = [
                            $record['title'],
                            $record['display_title'],
                            $record['list_title'],
                        ];
                        if (array_key_exists('alt_list_title', $record) && $record['alt_list_title']) {
                            foreach ($record['alt_list_title'] as $title) {
                                $titles[] = $title;
                            }
                        }
                        if (array_key_exists('alt_display_title', $record) && $record['alt_display_title']) {
                            foreach ($record['alt_display_title'] as $title) {
                                $titles[] = $title;
                            }
                        }
                        $record['titles'] = $titles;
                        break;
                }
            }
        }
        unset($record['display_title']);
        unset($record['alt_display_title']);
        unset($record['list_title']);
        unset($record['alt_list_title']);

        // HATEOAS
        $record['links'] = [
            [
                'rel' => "self",
                'href' => $this->getHateOASLink($record, "self")
            ]
        ];

        // if it's a group, introduce links to sub-type
        if ($record['type'] === 'group') {

            foreach ($this->validActivitiesTypes as $type) {
                $link = [
                    'rel' => $type,
                    'href' => $this->getHateOASLink($record, $type)
                ];
                $record['links'][] = $link;
            }

        }

        return $record;
    }

    private function canbeFuzzy($field){
        //fuzzyable if not exact
        if (strpos($field, '"') === false) {

            //replace bad char
            $field = str_replace(":", "", $field);
            $field = str_replace("(", "", $field);
            $field = str_replace(")", "", $field);

            // fuzzy
            $field = str_replace(" ", "*", $field);

            //for purl or uri search, don't put fuzzy
            if (strpos($field, "http") === false) {
                $field = "*".$field."*";
            }
        }
        return $field;
    }


    /**
     * Helper method
     * Returns the array with the key swapped
     * todo move to array_helper or some other helper
     *
     * @param $array
     * @param $fromKey
     * @param $toKey
     */
    private function changeKey($array, $fromKey, $toKey)
    {
        if (array_key_exists($fromKey, $array)) {
            $array[$toKey] = $array[$fromKey];
            unset($array[$fromKey]);
        } else {
            $array[$toKey] = null;
        }
        return $array;
    }

    /**
     * Helper method
     * Returns the HATEOAS Link for the current record
     * @param $record
     * @param $type
     * @return string
     */
    private function getHateOASLink($record = null, $type)
    {

        $current = "http://". $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        /**
         * hack
         * todo reimplement using a good url library
         * get everything up until /activities
         */
        $arr = explode("/activities", $current, 2);
        $first = $arr[0];
        $current = $first.'/activities';

        //remove last /
        if (substr($current, -1) == '/'){
            $current = substr($current, 0, -1);
        }

        $fragments = array();
        $fragments[] = $current;

        if ($type == "self") {
            if (in_array($record['type'], $this->validActivitiesTypes)) {
                $fragments[] = $record['type'].'s';
            } elseif ($record['type'] === 'group') {
                $fragments[] = "funders";
            }
            $fragments[] = $record['id'];
        } elseif (in_array($type, $this->validActivitiesTypes) && $record !== null) {
            if (in_array($record['type'], $this->validActivitiesTypes)) {
                $fragments[] = $record['type'].'s';
            } elseif ($record['type'] === 'group') {
                $fragments[] = "funders";
            }
            $fragments[] = $record['id'];
            $fragments[] = $type.'s';
        } elseif ($record === null) {
            $fragments[] = $type.'s';
        }

        return implode('/', $fragments);
    }
}
