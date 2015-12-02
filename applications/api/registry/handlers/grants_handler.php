<?php
namespace ANDS\API\Registry\Handler;

/**
 * Handles registry/grants
 * getGrants API
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class GrantsHandler extends Handler
{

    private $defaultGroups = '"National Health and Medical Research Council","Australian Research Council"';
    private $defaultType = "grant";

    /**
     * Handling the grants method
     * @return array
     */
    public function handle()
    {

        $principalInvestigator = null;
        $institution = null;

        $this->ci->load->library('solr');
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');

        foreach ($this->ci->input->get() as $name => $value) {

            $institution = ($name == 'institution') ? $value : null;
            $principalInvestigator = ($name == 'principalInvestigator') ? $value : null;

            //Determine which groups we are searching against
            if ($name == 'group' && $value != "") {
                $this->defaultGroups = '"' . $value . '"';
            }

            //display_title,researcher,year,institution
            if ($name == 'title' && $value != '') {
                $words = $this->getWords($value);
                foreach ($words as $word) {
                    $this->ci->solr->setOpt('fq', '+title_search:(' . $word . ')');
                }
            }

            //type
            if ($name == 'type' && ($value!='' || $value!='all')) {
                $this->defaultType = $value;
            }

            //identifier
            if (($name == 'id' || $name=='purl') && $value != '') {
                $this->ci->solr->setOpt('fq', '+identifier_value:*' . $value . '*');
            }

            if ($name == 'description' && $value != '') {
                $words = $this->getWords($value);
                foreach ($words as $word) {
                    $this->ci->solr->setOpt('fq', '+description:(' . $word . ')');
                }
            }

            if ($name == 'institution' && $value != '') {
                $this->ci->solr->setOpt('fq', '+related_party_multi_search:"' . $value . '"');
                //$this->ci->solr->setOpt('fq','+related_object_relation:"isManagedBy"');
            }

            if ($name == 'person' && $value != '') {
                $words = $this->getWords($value);
                foreach ($words as $word) {
                    $this->ci->solr->setOpt('fq', ' related_party_one_search:(' . $word . ') OR researchers:(' . $word . ')');
                }
                //$this->ci->solr->setOpt('fq','+related_object_class:"party"');
            }

            if ($name == 'principalInvestigator' && $value != '') {
                $words = $this->getWords($value);
                foreach ($words as $word) {
                    $this->ci->solr->setOpt('fq', '+related_party_one_search:(' . $word . ')');
                }
                //$this->ci->solr->setOpt('fq','+related_object_relation:"isPrincipalInvestigatorOf"');
            }
        }

        //type
        if ($this->defaultType != "grant") {
            $this->ci->solr->setOpt('fq', '+type:(' .$this->defaultType .')');
        }

        //execute
        $this->ci->solr->setOpt('fq', '+class:"activity"')->setOpt('rows', '20');
        $result = $this->ci->solr->executeSearch(true);

        //response setup
        $response = array(
            'numFound' => 0,
            'recordData' => array(),
            'query' => $this->ci->solr->constructFieldString()
        );

        /**
         * Populate the response based on the result returned
         *
         */
        foreach ($result['response']['docs'] as $result) {
            $ro = $this->ci->ro->getByID($result['id']);

            //if Object doesn't exist
            if (!$ro) break;

            //build relationships array of the object
            $relationships = false;
            $related = $ro->getRelatedObjectsByClassAndRelationshipType(array('party'), array());
            if (isset($related)) {
                $relationships = $this->processRelated($related);
            }

            //build identifiers array of the object
            $identifiers = false;
            if (isset($result['identifier_value'])) {
                $identifiers = $this->processIdentifiers($result['identifier_value'], $result['identifier_type']);
            }

            /**
             * Filter, canPass will determine if this object can enter the final array
             * todo Update business rule here for inline comment
             */
            $canPass = true;

            if (isset($institution) && isset($relationships['isManagedBy'])) {
                $canPass = false;
                if (is_array($relationships['isManagedBy'])) {
                    for ($i = 0; $i < sizeof($relationships['isManagedBy']); $i++) {
                        $words = $this->getWords($relationships['isManagedBy'][$i]);
                        for ($i = 0; $i < sizeof($institution); $i++) {
                            if (!$canPass) {
                                $canPass = in_array($institution[$i], $words);
                            }
                        }
                    }
                } else {
                    $words = $this->getWords($relationships['isManagedBy']);
                    for ($i = 0; $i < sizeof($institution); $i++) {
                        if (!$canPass) {
                            $canPass = in_array($institution[$i], $words);
                        }
                    }
                }
            }

            if (isset($principalInvestigator) && isset($relationships['isPrincipalInvestigatorOf'])) {
                $canPass = false;
                if (is_array($relationships['isPrincipalInvestigatorOf'])) {
                    for ($i = 0; $i < sizeof($relationships['isPrincipalInvestigatorOf']); $i++) {
                        $words = $this->getWords($relationships['isPrincipalInvestigatorOf'][$i]);
                        for ($i = 0; $i < sizeof($principalInvestigator); $i++) {
                            if (!$canPass) {
                                $canPass = in_array($principalInvestigator[$i], $words);
                            }
                        }
                    }
                } else {
                    $words = $this->getWords($relationships['isPrincipalInvestigatorOf']);
                    for ($i = 0; $i < sizeof($principalInvestigator); $i++) {
                        if (!$canPass) {
                            $canPass = in_array($principalInvestigator[$i], $words);
                        }
                    }
                }
            }

            //If this object pass the test, add it to the response array
            if ($canPass) {
                $response['numFound'] += 1;
                $response['recordData'][] = array(
                    'key' => $result['key'],
                    'slug' => $result['slug'],
                    'title' => $result['display_title'],
                    'type' => $result['type'],
                    'description' => isset($result['description']) ? $result['description'] : "",
                    'identifiers' => $result['identifier_value'],
                    'identifier_type' => $identifiers,
                    'relations' => $relationships
                );
            }
        }

        return $response;
    }

    /**
     * Helper method for handle()
     * Returns the relationships array filtered with additional information
     * @param $related
     * @return array
     */
    private function processRelated($related)
    {
        //build the relationship and only construct relationship for PUBLISHED records
        $relationships = array();
        for ($i = 0; $i < sizeof($related); $i++) {
            if (isset($related[$i]['relation_type']) && $related[$i]['status'] == 'PUBLISHED') {
                if (isset($relationships[$related[$i]['relation_type']])) {
                    $relationships[$related[$i]['relation_type']][] = $related[$i]['title'];
                } else {
                    $firstTitle = $related[$i]['title'];
                    $relationships[$related[$i]['relation_type']] = array();
                    $relationships[$related[$i]['relation_type']][] = $firstTitle;
                }
            }
        }

        //verify relationship count
        foreach ($relationships as $key => $relationship) {
            if (count($relationship) == 1) {
                $relationships[$key] = $relationship[0];
            }
        }
        return $relationships;
    }

    /**
     * Helper method for handle()
     * Returns the identifiers array in a better format
     * @param $value
     * @param $type
     * @return array
     */
    private function processIdentifiers($value, $type)
    {
        $identifiers = array();
        for ($i = 0; $i < sizeof($type); $i++) {
            if (isset($identifiers[$type[$i]])) {
                if (is_array($identifiers[$type[$i]])) {
                    $identifiers[$type[$i]][] = $value[$i];
                }
            } else {
                $identifiers[$type[$i]] = $value[$i];
            }
        }
        return $identifiers;
    }

    /**
     * Helper method
     * Returns words without the stop words
     * @param $string
     * @return array
     */
    private function getWords($string)
    {
        $invalid_characters = array("$", "%", "#", "<", ">", "|", '"', "'", "(", ")");
        $stopWords = array("a", "an", "and", "are", "as", "at", "be", "but", "by", "for", "if", "in", "into", "is", "it", "no", "not", "of", "on", "or", "s", "such", "t", "that", "the", "their", "then", "there", "these", "they", "this", "to", "was", "will", "with");
        $string = str_replace($invalid_characters, "", strtolower($string));
        $words = explode(" ", $string);
        $words = array_diff($words, $stopWords);
        return $words;
    }
}
