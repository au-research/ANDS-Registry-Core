<?php

/**
 * Class Activity_grants_extension
 * To extract all activity only sync values out
 *
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Activity_grants_extension extends ExtensionBase
{

    function __construct($ro_pointer)
    {
        parent::__construct($ro_pointer);
    }

    /**
     * Return the Funding Amount of the activity
     * description[type=fundingAmount]
     *
     * @param bool|false $gXPath
     * @return bool|mixed
     */
    function getFundingAmount($gXPath = false)
    {
        if (!$gXPath) {
            $gXPath = $this->getGXPath();
        }
        $fundingAmount = false;
        foreach ($gXPath->query('//ro:description[@type="fundingAmount"]') as $node) {
            $fundingAmount = preg_replace("/[^\d\.]+/", "", $node->nodeValue);
        }
        return $fundingAmount;
    }

    /**
     * Return the Funding Scheme of the activity
     * description[type=fundingScheme]
     * todo relatedObject[type=activity|program][relation=isPartOf|isFundedBy]
     *
     * @param bool|false $gXPath
     * @return bool|string
     */
    function getFundingScheme($gXPath = false)
    {
        $fundingScheme = false;

        //description[type=fundingScheme]
        if (!$gXPath) {
            $gXPath = $this->getGXPath();
        }
        foreach ($gXPath->query('//ro:description[@type="fundingScheme"]') as $node) {
            $fundingScheme = strip_tags(html_entity_decode($node->nodeValue));
        }

        /**
         * relatedObject[type=activity|program][relation=isPartOf|isFundedBy]
         * fundingScheme is currently a single String, having repeated would require some BI work and change of the schema
         * 7/12/2015 checking with BI for solution
         */

        return $fundingScheme;
    }

    /**
     * Returns Researchers
     * description[type=researchers]
     * DEPRECATED: relatedObject[class=party][type=person][relation=hasPrincipalInvestigator|relation=hasParticipant]
     * INSTEAD: relatedObject[class=party][type=person]
     * relatedInfo[type=party][relation=hasPrincipalInvestigator|hasParticipant]
     *
     * @param bool|false $gXPath
     * @param bool|false $relatedObjects
     * @return array
     */
    function getResearchers($gXPath = false, $relatedObjects = false)
    {
        if (!$gXPath) {
            $gXPath = $this->getGXPath();
        }

        $researchers = array();

        //description[type=researchers]
        foreach ($gXPath->query('//ro:description[@type="researchers"]') as $node) {
            $researchers[] = strip_tags(html_entity_decode($node->nodeValue));
        }

        //relatedInfo[type=party][relation=hasPrincipalInvestigator|hasParticipant]
        foreach ($gXPath->query('//ro:relatedInfo[@type="party"]') as $node) {
            foreach ($node->getElementsByTagName("relation") as $relationNode) {
                $type = $relationNode->getAttribute("type");
                if ($type == "hasPrincipalInvestigator" || $type == "hasParticipant") {
                    if ($titleNode = $node->getElementsByTagName("title")->item(0)) {
                        $researchers[] = strip_tags(html_entity_decode($titleNode->nodeValue));
                    }
                }
            }
        }

        //relatedObject[class=party][type=person]
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        }
        foreach ($relatedObjects as $relatedObject) {
            if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT) {
                if ($relatedObject['class'] == 'party'
                    && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'],
                        'type'))) == 'person'
                ) {
                    $researchers[] = $relatedObject['title'];
                }
            }
        }

        //remove duplicates
        $researchers = array_values(array_unique($researchers));
        return $researchers;
    }

    /**
     * Returns all the institutions participating in this research grant
     * relatedObject[relation=isManagedBy|hasParticipant][type=group][class=party]
     *
     * @param bool|false $relatedObjects
     * @return array
     */
    function getInstitutions($relatedObjects = false)
    {
        $institutions = array();
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        }
        if ($relatedObjects) {
            foreach ($relatedObjects as $relatedObject) {
                if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT) {
                    if ($relatedObject['class'] == 'party'
                        && ($relatedObject['relation_type'] == 'isManagedBy' || $relatedObject['relation_type'] == 'hasParticipant')
                        && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'],
                            'type'))) == 'group'
                    ) {
                        $institutions[] = $relatedObject['title'];
                    }
                }
            }
        }

        //remove duplicates
        $institutions = array_values(array_unique($institutions));
        return $institutions;
    }

    /**
     * Returns the Administering Institution
     * relatedObject[relation=isManagedBy][class=party][type!=person]
     *
     * @param bool|false $relatedObjects
     * @return array
     */
    function getAdministeringInstitution($relatedObjects = false)
    {
        $administeringInstitution = array();
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        }
        if ($relatedObjects) {
            foreach ($relatedObjects as $relatedObject) {
                if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT) {
                    if ($relatedObject['class'] == 'party'
                        && $relatedObject['relation_type'] == 'isManagedBy'
                        && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'],
                            'type'))) != 'person'
                    ) {
                        $administeringInstitution[] = $relatedObject['title'];
                    }
                }
            }
        }

        return $administeringInstitution;
    }

    /**
     * Returns the Funder of a record
     * relatedObject[relation=isFundedBy][class=party][type!=person]
     * relatedInfo[type=party][relation=isFundedBy]
     *
     * @param bool|false $gXPath
     * @param bool|false $relatedObjects
     * @param bool       $recursive
     * @param array      $processed
     * @return array
     */
    function getFunders($gXPath = false, $relatedObjects = false, $recursive = true, $processed = array())
    {

        $funders = array();


        if (sizeof($processed) == 0) {
            array_push($processed, $this->ro->id);
        }
        //relatedInfo[type=party][relation=isFundedBy]
        if (!$gXPath) {
            $gXPath = $this->getGXPath();
        }
        foreach ($gXPath->query("//ro:relatedInfo") as $node) {
            foreach ($node->getElementsByTagName("relation") as $relationNode) {
                $type = $relationNode->getAttribute("type");
                if ($type == "isFundedBy") {
                    if ($titleNode = $node->getElementsByTagName("title")->item(0)) {
                        $funders[] = strip_tags(html_entity_decode($titleNode->nodeValue));
                    }
                }
            }
        }

        //relatedObject[relation=isFundedBy][class=party][type!=person]
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        }
        if ($relatedObjects) {
            foreach ($relatedObjects as $relatedObject) {

                if (isset($relatedObject['status']) && $relatedObject['status'] == PUBLISHED) {
                    if ($relatedObject['class'] == 'party'
                        && $relatedObject['relation_type'] == 'isFundedBy'
                        && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'],
                            'type'))) != 'person'
                    ) {
                        $funders[] = $relatedObject['title'];
                    }
                }

                // recursively find all funders that this node `isPartOf`
                if ($recursive === true
                    && $relatedObject['relation_type'] == 'isPartOf'
                    && $relatedObject['origin'] != 'REVERSE_INT'
                    && !in_array($relatedObject['registry_object_id'], $processed)
                ) {
                    array_push($processed, $relatedObject['registry_object_id']);
                    $record = $this->_CI->ro->getByID($relatedObject['registry_object_id']);
                    $relatedFunders = $record->getFunders(false, false, $recursive, $processed);

                    if (sizeof($relatedFunders) > 0) {
                        $funders = array_merge($funders, $relatedFunders);
                    }
                    unset($record);
                }

            }
        }

        //remove duplicates
        $funders = array_values(array_unique($funders));
        return $funders;
    }


    /**
     * Return the principal investigator
     * relatedObject[class=party][type=person][relation=hasPrincipalInvestigator]
     * relatedInfo[type=party][relation=hasPrincipalInvestigator]
     *
     * @param bool|false $gXPath
     * @param bool|false $relatedObjects
     * @return array
     */
    function getPrincipalInvestigator($gXPath = false, $relatedObjects = false)
    {
        $principalInvestigator = array();

        //relatedInfo[type=party][relation=hasPrincipalInvestigator]
        if (!$gXPath) {
            $gXPath = $this->getGXPath();
        }
        foreach ($gXPath->query("//ro:relatedInfo") as $node) {
            foreach ($node->getElementsByTagName("relation") as $relationNode) {
                $type = $relationNode->getAttribute("type");
                if ($type == "hasPrincipalInvestigator") {
                    if ($titleNode = $node->getElementsByTagName("title")->item(0)) {
                        $principalInvestigator[] = strip_tags(html_entity_decode($titleNode->nodeValue));
                    }
                }
            }
        }

        //relatedObject[class=party][type=person][relation=hasPrincipalInvestigator]
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        }
        if ($relatedObjects) {
            foreach ($relatedObjects as $relatedObject) {
                if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT) {
                    if ($relatedObject['class'] == 'party'
                        && $relatedObject['relation_type'] == 'hasPrincipalInvestigator'
                        && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'],
                            'type'))) == 'person'
                    ) {
                        $principalInvestigator[] = $relatedObject['title'];
                    }
                }
            }
        }

        //remove duplicates
        $principalInvestigator = array_values(array_unique($principalInvestigator));
        return $principalInvestigator;
    }

    /**
     * Returns the Activity Status
     * Works off the earliest and latest dates in existenceDates
     *
     * @param bool|false $xml
     * @return string
     */
    function getActivityStatus($xml = false)
    {
        $activityStatus = 'other';
        if (!$xml) {
            $xml = $this->ro->getSimpleXML();
        }
        foreach ($xml->xpath('//ro:existenceDates') AS $date) {
            $now = time();
            $start = false;
            $end = false;
            if ($date->startDate) {
                if (strlen(trim($date->startDate)) == 4) {
                    $date->startDate = "Jan 1, " . $date->startDate;
                }
                $start = strtotime($date->startDate);
            }

            if ($date->endDate) {
                if (strlen(trim($date->endDate)) == 4) {
                    $date->endDate = "Dec 31, " . $date->endDate;
                }
                $end = strtotime($date->endDate);
            }

            if ($start || $end) {
                $activityStatus = 'PENDING';
                if (!$start || $start < $now) {
                    $activityStatus = 'ACTIVE';
                }
                if ($end && $end < $now) {
                    $activityStatus = 'CLOSED';
                }
            }
        }

        return $activityStatus;
    }

    /**
     * Recursively get all of the activities that are related to this object via a specific relation
     * Use for getting all funded activities for a funder to get all programs
     *
     * @param bool|false $relatedObjects
     * @param string     $relation
     * @param array      $processed
     * @param bool       $recursive
     * @return array
     */
    public function getChildActivities(
        $relatedObjects = false,
        $relation = 'isFundedBy',
        $processed = array(),
        $recursive = true
    ) {
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        }

        if ($this->ro->class == 'party') {
            $relation = 'isFundedBy';
        } elseif ($this->ro->class == 'activity') {
            $relation = 'isPartOf';
        }

        //hard limit on how many node will be processed for child activities
        $limit = 300;
        if (sizeof($processed) > $limit) {
            return array();
        }

        $result = array();
        if ($relatedObjects) {
            foreach ($relatedObjects as $relatedObject) {

                if ($relatedObject['relation_type'] == $relation
//                    && $relatedObject['origin'] != 'REVERSE_INT'
                    && !in_array($relatedObject['registry_object_id'], $processed)
                ) {

                    $result[] = $relatedObject;
                    array_push($processed, $relatedObject['registry_object_id']);
                    if ($recursive) {
                        $record = $this->_CI->ro->getByID($relatedObject['registry_object_id']);
                        $childs = $record->getChildActivities(false, 'isPartOf', $processed, true);
                        if (sizeof($childs) > 0) {
                            $result = array_merge($result, $childs);
                        }
                    }
                }
            }
        }


        return $result;
    }

    /**
     * get the Grants Structured Data for this object
     *
     * @param bool|false $relatedObjects
     * @return array
     */
    public function getStructuredGrantsAtNode($relatedObjects = false)
    {
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        }

        $dataOutputs = $this->ro->getDirectDataOutput($relatedObjects);
        $structure = $this->getStructuredGrants($relatedObjects);
        $publications = $this->getDirectPublication();

        $result = array();
        if (sizeof($structure) > 0) {
            $result['program'] = $structure;
        }
        if (sizeof($dataOutputs) > 0) {
            $result['data_output'] = $dataOutputs;
        }
        if (sizeof($publications) > 0) {
            $result['publications'] = $publications;
        }

        return $result;
    }

    /**
     * Generate a strucutred grants
     * Recursively
     * Displaying all the program and data outputs that belongs to the node in a tree
     *
     * @param bool|false $relatedObjects
     * @param array      $processed
     * @return array
     */
    public function getStructuredGrants($relatedObjects = false, $processed = array())
    {
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        }
        $result = array();
        $childActivities = $this->getChildActivities($relatedObjects, 'isFundedBy', array(), false);
        foreach ($childActivities as &$childActivity) {
            if (!in_array($childActivity['registry_object_id'], $processed)) {
                array_push($processed, $childActivity['registry_object_id']);
                $record = $this->_CI->ro->getByID($childActivity['registry_object_id']);

                //get data outputs
                $dataOutputs = array();
                $publications = array();
                $related = $record->getAllRelatedObjects(false, false, true);
                foreach ($related as $relatedObject) {
                    $record = $this->_CI->ro->getByID($relatedObject['registry_object_id']);
                    $dataOutputs = array_merge($dataOutputs, $record->getDirectDataOutput());
                    $publications = array_merge($publications, $record->getDirectPublication());
                }
                if (sizeof($dataOutputs) > 0) {
                    $childActivity['data_output'] = $dataOutputs;
                }
                if (sizeof($publications) > 0) {
                    $childActivity['publications'] = $publications;
                }


                //get nested activities
                $children = $record->getStructuredGrants(false, $processed);
                if (sizeof($children) > 0) {
                    $childActivity['program'] = $children;
                }

                unset($record);
                unset($children);
                unset($dataOutputs);
                array_push($result, $childActivity);
            }
        }

        //add stuff for the current node
        $dataOutputs = $this->ro->getDirectDataOutput($relatedObjects);
        $publications = $this->getDirectPublication();

        if (sizeof($dataOutputs) > 0) {
            $result['data_output'] = $dataOutputs;
        }
        if (sizeof($publications) > 0) {
            $result['publications'] = $publications;
        }

        return $result;
    }

    /**
     * Get all of the data output from an activity
     * relatedObject[class=collection][relation=isOutputOf] from a recursively generated list of child activities
     *
     * @param bool|false $childActivities
     * @param bool|false $relatedObjects
     * @param bool       $recursive
     * @return array
     */
    public function getDataOutput($childActivities = false, $relatedObjects = false, $recursive = true)
    {
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        }
        if (!$childActivities) {
            $childActivities = $this->ro->getChildActivities($relatedObjects, 'isPartOf', array(), $recursive);
        }

        $result = array();
        foreach ($childActivities as $activity) {
            $activityObject = $this->_CI->ro->getByID($activity['registry_object_id']);
            $dataOutputs = $activityObject->getDirectDataOutput();
            if (sizeof($dataOutputs) > 0) {
                $result = array_merge($result, $dataOutputs);
            }
        }


        //self
        $directOutput = $this->ro->getDirectDataOutput();
        if (sizeof($directOutput) > 0) {
            $result = array_merge($result, $directOutput);
        }


        //remove duplicates
        $result = array_values(array_map("unserialize", array_unique(array_map("serialize", $result))));


        return $result;
    }

    public function getDirectDataOutput($relatedObjects = false)
    {
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        } else {
            $relatedObjects = array();
        }

        $result = array();
        foreach ($relatedObjects as $relatedObject) {
            if ($relatedObject['relation_type'] == 'isOutputOf'
                || $relatedObject['relation_type'] == 'hasOutput'
                || $relatedObject['relation_type'] == 'outputs'
            ) {
                $result[] = $relatedObject;
            }
            //todo relatedInfo of relation_type isOutputOf type activity
        }
        return $result;
    }

    /**
     * Get all the publications from an activity and all of it's child activities
     * For each of the listed activity, get the purl assigned to it
     * For each of the purl, query Trove API to get the publication
     *
     * @param bool|false $childActivitites
     * @param bool|false $relatedObjects
     * @return array
     */
    public function getPublications($childActivitites = false, $relatedObjects = false)
    {
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        }
        if (!$childActivitites) {
            $childActivitites = $this->ro->getChildActivities($relatedObjects);
        }

        $result = array();
        // Construct a list of purls from all the child activities
        if ($childActivitites) {
            foreach ($childActivitites as $activity) {
                //find all identifier type purl
                $record = $this->_CI->ro->getByID($activity['registry_object_id']);
                $result = array_merge($result, $record->getDirectPublication());
                unset($record);
            }
        }

        //self
        $directPublication = $this->ro->getDirectPublication($relatedObjects);
        if (sizeof($directPublication) > 0) {
            $result = array_merge($result, $directPublication);
        }

        return $result;
    }

    public function getDirectPublication()
    {

        //collect all the purls
        $purls = array();
        $identifiers = $this->ro->getIdentifiers();
        foreach ($identifiers as $identifier) {
            if ($identifier['identifier_type'] == 'purl') {
                $purls[] = $identifier['identifier'];
            }
        }

        //resolve purls
        $this->_CI->load->library('TroveAPI');
        $result = array();
        foreach ($purls as $purl) {
            $troveAPIResponse = $this->_CI->troveapi->resolveQuery('"' . $purl . '"', 'article', 'workverions');
            if ($troveAPIResponse) {
                foreach ($troveAPIResponse['response']['zone'] as $zone) {
                    if ($zone['name'] == 'article' && isset($zone['records']) && isset($zone['records']['work'])) {
                        foreach ($zone['records']['work'] as $work) {
                            $result[] = [
                                'purl' => $purl,
                                'id' => $work['id'],
                                'troveUrl' => $work['troveUrl'],
                                'title' => $work['title'],
                                'identifier' => $work['identifier']
                            ];
                        }
                    }
                }
            }
        }

        $result = array_values(array_map("unserialize", array_unique(array_map("serialize", $result))));
        return $result;
    }


    /**
     * Helper method to return the gXPath
     *
     * @return DOMXpath
     */
    public function getGXPath()
    {
        $rifDom = new DOMDocument();
        $rifDom->loadXML($this->ro->getRif());
        $gXPath = new DOMXpath($rifDom);
        $gXPath->registerNamespace('ro', RIFCS_NAMESPACE);
        return $gXPath;
    }
}