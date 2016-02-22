<?php
namespace ANDS\API\Registry\Handler;

/**
 * Handles registry/grants
 * getGrants API
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class GrantsHandlerV2 extends Handler
{

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

        //identifier
        $identifier = (isset($params['identifier'])) ? $params['identifier'] : null;
        if ($identifier) {
            $this->ci->solr->setOpt('fq', '+identifier_value:("' . $identifier . '")');
        }
        //individual id
        $id = (isset($params['id'])) ? $params['id'] : null;
        if ($id) {
            $this->ci->solr->setOpt('fq', '+identifier_value:*'.urldecode($id).'*');
        }
        //title
        $title = (isset($params['title'])) ? $params['title'] : null;
        if ($title) {
            $this->ci->solr->setOpt('fq', 'title_search:(' . $title . ')');
        }

        //institution
        if ($institutions = (isset($params['institution'])) ? $params['institution'] : null) {
            $this->ci->solr->setOpt('fq', '+administering_institution_search:"' . $institutions . '"');
        }

        //principalInvestigator
        if ($principalInvestigator = (isset($params['principalInvestigator'])) ? $params['principalInvestigator'] : null) {
            $this->ci->solr->setOpt('fq', '+principal_investigator_search:"' . $principalInvestigator . '"');
        }

        //person
        if ($person = (isset($params['person'])) ? $params['person'] : null) {
            $this->ci->solr->setOpt('fq', '+researchers_search:"' . $person . '"');
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
        //default to 30
        if ($rows = (isset($params['rows'])) ? $params['rows'] : 30) {
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
            'totalFound' => $result['response']['numFound'],
            'numFound' => 0,
            'recordData' => array()
        );

        if ($this->ci->input->get('debug')) {
            $response['query'] = urldecode($this->ci->solr->constructFieldString());
        }

        /**
         * Populate the response based on the result returned
         */
        foreach ($result['response']['docs'] as $result) {
            $ro = $this->ci->ro->getByID($result['id']);

            //if Object doesn't exist
            if (!$ro) break;

            //cache relatedObjects for passing into functions that needed it, to save processing time
            $relatedObjects = $ro->getAllRelatedObjects(false, false, true);

            //cache gXPath
            $gXPath = $ro->getGXPath();

            //cache XML
            $xml = $ro->getSimpleXML();


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
             */
            $funder = $ro->getFunders($gXPath, $relatedObjects);
            if (sizeof($funder) > 0) {
                $data['funder'] = $funder;
            }

            /**
             * Getting Identifiers
             * identifier of all type
             */
            $id = array();
            //identifiers should've been generated from before
            if ($identifiers) {
                foreach ($identifiers as $identifier) {
                    $id[] = $identifier['identifier'];
                }
            }
            $data['identifier'] = $id;


            /**
             * Researchers
             * A list of researchers named on the awarded grant
             * description[type=researchers]
             * or
             * semicolon-separated list of names generated from
             * name[type=primary] of relatedObject with relation=hasPrincipalInvestigator or relation=hasParticipant
             * title of relatedInfo with relation=hasPrincipalInvestigator or relation=hasParticipant
             */
            $researchers = $ro->getResearchers($gXPath, $relatedObjects);
            if (sizeof($researchers) > 0) {
                $data['researchers'] = $researchers;
            }


            /**
             * PrincipalInvestigator
             * name[type=primary] of relatedObject with relation=hasPrincipalInvestigator or relation=hasParticipant
             * title of relatedInfo with relation=hasPrincipalInvestigator or relation=hasParticipant
             */
            $principalInvestigator = $ro->getPrincipalInvestigator($gXPath, $relatedObjects);
            if (sizeof($principalInvestigator) > 0) {
                $data['principalInvestigator'] = $principalInvestigator;
            }

            /**
             * Institution
             * semicolon-separated list of org names
             * from
             * name[type=primary] of relatedObject party group with relation=isManagedBy or relation=hasParticipant
             */
            $institutions = $ro->getInstitutions($relatedObjects);
            if (sizeof($institutions) > 0) {
                $data['institutions'] = $institutions;
            }

            /**
             * Managing Institution
             * name[type=primary] of relatedObject party group with relation=isManagedBy
             */
            $managingInstitution = $ro->getAdministeringInstitution($relatedObjects);
            if (sizeof($managingInstitution) > 0) {
                $data['managingInstitution'] = $managingInstitution;
            }

            /**
             * fundingAmount
             * description[type=fundingAmount]
             */
            $fundingAmount = $ro->getFundingAmount($gXPath);
            $data['fundingAmount'] = $fundingAmount;

            /**
             * fundingScheme
             * description[type=fundingScheme]
             */
            $fundingScheme = $ro->getFundingScheme($gXPath);
            $data['fundingScheme'] = $fundingScheme;

            $dateFormat = "Y-m-d H:i:s";

            /**
             * startDate
             * Format W3DTF
             * existenceDate/startDate
             */
            $startDate = $ro->getExistenceDate("startDate", $dateFormat, $xml);
            $data['startDate'] = $startDate;

            /**
             * endDate
             * existenceDate/endDate
             */
            $endDate = $ro->getExistenceDate("endDate", $dateFormat, $xml);
            $data['endDate'] = $endDate;

            /**
             * dateTimeCreated
             */
            $dateTimeCreated = $ro->created;
            $data['dateTimeCreated'] = date($dateFormat, $dateTimeCreated);

            /**
             * dateTimeModified
             */
            $dateTimeModified = $ro->updated;
            $data['dateTimeModified'] = date($dateFormat, $dateTimeModified);


            //add data to the response array
            $response['numFound'] += 1;
            $response['recordData'][] = $data;

            //save memory by clearing the ro object
            unset($ro);
        }

        return $response;
    }


    /**
     * Helper method
     * Returns words without the stop words
     * todo move to presentation_helper or some other helper for global usage
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
