<?php
namespace ANDS\API\Registry\Handler;

/**
 * Handles registry/grants
 * getGrants API
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class GrantsHandler extends Handler
{

//    private $defaultGroups = '"National Health and Medical Research Council","Australian Research Council"';
//    private $defaultType = "grant";

    /**
     * Handling the grants method
     * @return array
     */
    public function handle()
    {
        //load libraries for use
        $this->ci->load->library('solr');
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');

        $params = $this->ci->input->get();

        //Only search for activity
        $this->ci->solr->setOpt('fq', '+class:"activity"');

        //purl
        if ($purl = (isset($params['purl'])) ? $params['purl'] : null) {
            $this->ci->solr->setOpt('fq', '+identifier_value_search:(' . $purl . ')');
        }

        //type
        if ($type = (isset($params['type'])) ? $params['type'] : null) {
            $this->ci->solr->setOpt('fq', '+type:("' . $type . '")');
        }

        //funder
        if ($funder = (isset($params['funder'])) ? $params['funder'] : null) {
            $this->ci->solr->setOpt('fq', '+funders_search:(' . $funder . ')');
        }

        //grantid
        $grantid = (isset($params['grantid'])) ? $params['grantid'] : null;
        if ($grantid) {
            $this->ci->solr->setOpt('fq', '+identifier_value:("' . $grantid . '")');
        }

        //title without stopwords
        $title = (isset($params['title'])) ? implode(' ', $this->getWords($params['title'])) : null;
        if ($title) {
            $this->ci->solr->setOpt('fq', 'title_search:(' . $title . ')');
        }

        //institution without stopwords
        if ($institution = (isset($params['institution'])) ? implode(' ', $this->getWords($params['institution'])) : null) {
            $this->ci->solr->setOpt('fq', '+administering_institution_search:(' . $institution . ')');
        }

        //todo principalInvestigator param


        //person without stopwords
        if ($person = (isset($params['person'])) ? implode(' ', $this->getWords($params['person'])) : null) {
            $this->ci->solr->setOpt('fq', '+researchers:(' . $person . ')');
        }

        //status
        if ($status = (isset($params['status'])) ? $params['status'] : null) {
            $this->ci->solr->setOpt('fq', '+activity_status:(' . $status . ')');
        }

        //addedSince
        if ($addedSince = (isset($params['addedSince'])) ? $params['addedSince'] : null) {
            //convert to SOLR timestamp
            $addedSince = date('c', strtotime($addedSince)) . 'Z';
            $this->ci->solr->setOpt('fq', '+record_created_timestamp:[' . $addedSince . ' TO *]');
        }

        //modifiedSince
        if ($modifiedSince = (isset($params['modifiedSince'])) ? $params['modifiedSince'] : null) {
            //convert to SOLR timestamp
            $modifiedSince = date('c', strtotime($modifiedSince)) . 'Z';
            $this->ci->solr->setOpt('fq', '+record_modified_timestamp:[' . $modifiedSince . ' TO *]');
        }

        //rows
        if ($rows = (isset($params['rows'])) ? $params['rows'] : 999) {
            $this->ci->solr->setOpt('rows', $rows);
        }

        //start
        if ($start = (isset($params['start'])) ? $params['start'] : 0) {
            $this->ci->solr->setOpt('start', $start);
        }

        //execute search and store the result
        $result = $this->ci->solr->executeSearch(true);

        //response setup
        $response = array(
            'numFound' => 0,
            'recordData' => array(),
            'query' => $this->ci->solr->constructFieldString()
        );

        /**
         * Populate the response based on the result returned
         */
        foreach ($result['response']['docs'] as $result) {
            $ro = $this->ci->ro->getByID($result['id']);

            //if Object doesn't exist
            if (!$ro) break;

            //build relationships array of the object
            //todo check if we actually need to do a canPass here
//            $relationships = false;
//            $related = $ro->getRelatedObjectsByClassAndRelationshipType(array('party'), array());
//            if (isset($related)) {
//                $relationships = $this->processRelated($related);
//            }

            /**
             * Filter, canPass will determine if this object can enter the final array
             * due to limitation on the SOLR indexing
             */
            $canPass = true;

            //todo fix
//            if (isset($principalInvestigator) && isset($relationships['isPrincipalInvestigatorOf'])) {
//                $canPass = false;
//                if (is_array($relationships['isPrincipalInvestigatorOf'])) {
//                    for ($i = 0; $i < sizeof($relationships['isPrincipalInvestigatorOf']); $i++) {
//                        $words = $this->getWords($relationships['isPrincipalInvestigatorOf'][$i]);
//                        for ($i = 0; $i < sizeof($principalInvestigator); $i++) {
//                            if (!$canPass) {
//                                $canPass = in_array($principalInvestigator[$i], $words);
//                            }
//                        }
//                    }
//                } else {
//                    $words = $this->getWords($relationships['isPrincipalInvestigatorOf']);
//                    for ($i = 0; $i < sizeof($principalInvestigator); $i++) {
//                        if (!$canPass) {
//                            $canPass = in_array($principalInvestigator[$i], $words);
//                        }
//                    }
//                }
//            }


            // End searching logic
            // do not put the response in if record does not meet criteria
            if (!$canPass) break;


            //start displaying logic

            //data is the response object to add to the response array
            $data = array(
                'title' => $result['display_title'],
                'key' => $result['key']
            );

            /**
             * Getting the purl for the object to display in response
             * content of identifier[type=purl]
             */
            $purl = null;
            if ($identifiers = $ro->getIdentifiers()) {
                foreach ($identifiers as $identifier) {
                    $purl = (!$purl && $identifier['identifier_type'] == 'purl') ? $identifier['identifier'] : $purl;
                }
            }
            $data['purl'] = $purl;

            /**
             * Description
             * description[type=brief] or description[type=full]
             * todo implement Description based on ro description
             */
            $data['description'] = isset($result['description']) ? $result['description'] : "";

            /**
             * Getting the funder for the object
             * nameType=primary of a relatedObject party type=group with relation isFundedBy
             * or
             * title of a relatedInfo party with relation isFundedBy
             * todo implement funder
             */
            $funder = null;

            /**
             * Getting grant id
             * identifier[type=local] or identifier[type=arc] or identifier[type=nhmrc]
             */
            $grantid = null;
            //identifiers should've been generated from before
            if ($identifiers) {
                foreach ($identifiers as $identifier) {
                    if (!$grantid) {
                        $type = $identifier['identifier_type'];
                        $grantid = ($type == 'nhmrc' || $type == 'arc' || $type == 'local') ? $identifier['identifier'] : $grantid;
                    }
                }
            }
            $data['grantid'] = $grantid;

            /**
             * Researchers
             * A list of researchers named on the awarded grant
             * description[type=researchers]
             * or
             * semicolon-separated list of names generated from
             * name[type=primary] of relatedObject with relation=hasPrincipalInvestigator or relation=hasParticipant
             * title of relatedInfo with relation=hasPrincipalInvestigator or relation=hasParticipant
             * todo implement Researchers
             */
            $researchers = null;

            /**
             * PrincipalInvestigator
             * name[type=primary] of relatedObject with relation=hasPrincipalInvestigator or relation=hasParticipant
             * title of relatedInfo with relation=hasPrincipalInvestigator or relation=hasParticipant
             * todo implement PrincipalInvestigator
             */
            $principalInvestigator = null;

            /**
             * Institution
             * semicolon-separated list of org names
             * from
             * name[type=primary] of relatedObject party group with relation=isManagedBy or relation=hasParticipant
             * todo implement Institution
             */
            $institution = null;

            /**
             * Managing Institution
             * name[type=primary] of relatedObject party group with relation=isManagedBy
             * todo implement $managingInstitution
             */
            $managingInstitution = null;

            /**
             * fundingAmount
             * description[type=fundingAmount]
             * todo implement fundingAmount
             */
            $fundingAmount = null;

            /**
             * fundingScheme
             * description[type=fundingScheme]
             * todo implement fundingScheme
             */
            $fundingScheme = null;

            /**
             * startDate
             * Format W3DTF
             * existenceDate/startDate
             * todo implement startDate
             */
            $startDate = null;

            /**
             * endDate
             */
            $endDate = null;

            /**
             * dateTimeCreated
             */
            $dateTimeCreated = null;

            /**
             * dateTimeModified
             */
            $dateTimeModified = null;

            /**
             * Backward compatibility
             * relations/isFundedBy
             * relations/isManagedBy
             * relations/isPrincipalInvestigatorOf
             * relations/isParticipantIn
             * todo backward compatibility
             */

            //sanity check again
            if ($canPass) {
                $response['numFound'] += 1;
                $response['recordData'][] = $data;
            }

            //save memory by clearing the ro object
            unset($ro);
        }

        return $response;
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
