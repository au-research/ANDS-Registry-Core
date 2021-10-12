<?php

/**
 * Class Activity_grants_extension
 * To extract all activity only sync values out
 *
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Activity_grants_extension extends ExtensionBase
{

    /**
     * Activity_grants_extension constructor.
     *
     * @param $ro_pointer
     */
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
     * Return the landingPage of the activity
     * //ro:location/ro:address/ro:electronic[@type="landingPage"]
     *
     * @param bool|false $gXPath
     * @return bool|mixed
     */
    function getLandingPage($gXPath = false)
    {
        if (!$gXPath) {
            $gXPath = $this->getGXPath();
        }
        $landingPage = false;
        foreach ($gXPath->query('//ro:location/ro:address/ro:electronic[@target="landingPage"]') as $node) {
            $landingPage = $node->nodeValue;
        }
        return $landingPage;
    }


    /**
     * Return the subjects of the activity
     * description[type=subject]
     *
     * @param bool|false $gXPath
     * @return bool|mixed
     */
    function getSubjects()
    {
        $subjectsResolved = array();
        $this->_CI->load->library('vocab');
        $sxml = $this->ro->getSimpleXML();
        $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
        $subjects = $sxml->xpath('//ro:subject');
        foreach ($subjects AS $subject)
        {
            $type = (string)$subject["type"];
            $value = (string)$subject;
            if(!array_key_exists($value, $subjectsResolved))
            {
                $resolvedValue = $this->_CI->vocab->resolveSubject($value, $type);
                $subjectsResolved[$value] = array('type'=>$type, 'value'=>$value, 'resolved'=>$resolvedValue['value'], 'uri'=>$resolvedValue['about']);
                if($resolvedValue['uriprefix'] != 'non-resolvable')
                {
                    $broaderSubjects = $this->_CI->vocab->getBroaderSubjects($resolvedValue['uriprefix'],$value);
                    foreach($broaderSubjects as $broaderSubject)
                    {
                        $subjectsResolved[$broaderSubject['notation']] = array('type'=>$type, 'value'=>$broaderSubject['notation'], 'resolved'=>$broaderSubject['value'], 'uri'=>$broaderSubject['about']);
                    }
                }
            }
        }
        return $subjectsResolved;
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
            $researchersString = strip_tags(html_entity_decode($node->nodeValue));
            $researchersDescriptions = explode(';', $researchersString);
            foreach ($researchersDescriptions as $key=>&$researcher) {
                $researcher = trim($researcher);
                if ($researcher === "") unset($researchersDescriptions[$key]);
            }
            $researchers = array_merge($researchers, $researchersDescriptions);
        }

        //relatedInfo[type=party][relation=hasPrincipalInvestigator|hasParticipant]
        foreach ($gXPath->query('//ro:relatedInfo[@type="party"]') as $node) {
            foreach ($node->getElementsByTagName("relation") as $relationNode) {
                $type = $relationNode->getAttribute("type");
                if ($type == "isPrincipalInvestigator" || $type == "hasParticipant") {
                    if ($titleNode = $node->getElementsByTagName("title")->item(0)) {
                        $researchers[] = strip_tags(html_entity_decode($titleNode->nodeValue));
                    }
                }
            }
        }

        //relatedObject[class=party][type=person]
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }

        $relatedObjects = $this->filterValidRelatedObjects($relatedObjects);

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
     * DEPRECATED: relatedObject[relation=isManagedBy|hasParticipant][type=group][class=party]
     * DEPRECATED: relatedObject[type=group][class=party]
     * INSTEAD: relatedObject[type=group][class=party][relation!=isFundedBy][relation!=isFunderBy]
     * @param bool|false $relatedObjects
     * @return array
     */
    function getInstitutions($relatedObjects = false)
    {
        $institutions = array();
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }
        if ($relatedObjects) {
            $relatedObjects = $this->filterValidRelatedObjects($relatedObjects);
            foreach ($relatedObjects as $relatedObject) {

                if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT) {
                    if ($relatedObject['class'] == 'party'
                        && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'],
                            'type'))) == 'group'
                        && $relatedObject['relation_type'] != 'isFundedBy'
                        && $relatedObject['relation_type'] != 'isFunderOf'
                        && $relatedObject['relation_type'] != 'funds'
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
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }
        if ($relatedObjects) {
            $relatedObjects = $this->filterValidRelatedObjects($relatedObjects);
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
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }
        if ($relatedObjects) {
            $relatedObjects = $this->filterValidRelatedObjects($relatedObjects);
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

                    // check if the record exists, then get the funders of it
                    if ($record = $this->_CI->ro->getByID($relatedObject['registry_object_id']) ) {
                        $relatedFunders = $record->getFunders(false, false, $recursive, $processed);
                        if (sizeof($relatedFunders) > 0) {
                            $funders = array_merge($funders, $relatedFunders);
                        }
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
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }
        if ($relatedObjects) {
            $relatedObjects = $this->filterValidRelatedObjects($relatedObjects);
            foreach ($relatedObjects as $relatedObject) {


                if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT
                    && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'],'type'))) == 'person') {
                    $isValidChild = (($relatedObject['relation_type'] == 'hasPrincipalInvestigator' && $relatedObject['origin'] == 'EXPLICIT')
                        || ($relatedObject['relation_type'] == 'isPrincipalInvestigator' && startsWith($relatedObject['origin'],"REVERSE")));
                    if ($isValidChild )                     {
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
     * @param array      $processed
     * @param bool       $recursive
     * @return array
     * @internal param string $relation
     */
    public function getChildActivities(
        $relatedObjects = false,
        $processed = array(),
        $recursive = true
    ) {
        if ($relatedObjects === false) {
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }

        //hard limit on how many node will be processed for child activities
        $limit = 300;
        if (sizeof($processed) > $limit) {
            return array();
        }

        $result = array();
        if ($relatedObjects) {
            $relatedObjects = $this->filterValidRelatedObjects($relatedObjects);
            foreach ($relatedObjects as $relatedObject) {

                //setting the condition
                $isValidChild = false;
                if ($this->ro->class == 'party') {
                    //for a party, find all EXPLICIT funds and REVERSE_INT isFundedBy
                    $isValidChild = (($relatedObject['relation_type'] == 'funds' && $relatedObject['origin'] == 'EXPLICIT')
                        || ($relatedObject['relation_type'] == 'isFundedBy' && startsWith($relatedObject['origin'],"REVERSE"))
                        || ($relatedObject['relation_type'] == 'isFunderOf' && $relatedObject['origin'] == 'EXPLICIT'));

                    $isValidChild = $isValidChild || ($relatedObject['relation_type'] == 'isFundedBy' && $relatedObject['origin'] == "IDENTIFIER REVERSE");

                } elseif ($this->ro->class == 'activity') {
                    //for an activity, find all explicit partOf and reverse isPartOf
                    $isValidChild = (($relatedObject['relation_type'] == 'hasPart' && $relatedObject['origin'] == 'EXPLICIT')
                        || ($relatedObject['relation_type'] == 'isPartOf' && startsWith($relatedObject['origin'],
                                "REVERSE")));
                }

                //do not want to check recursively this child again
                $isValidChild = $isValidChild && !in_array($relatedObject['registry_object_id'], $processed);

                //only relates to PUBLISHED records
                $isValidChild = $isValidChild && $relatedObject['status']=='PUBLISHED';

                if ($isValidChild) {
                    $result[] = $relatedObject;
                    array_push($processed, $relatedObject['registry_object_id']);
                    if ($recursive) {
                        $record = $this->_CI->ro->getByID($relatedObject['registry_object_id']);
                        $childs = $record->getChildActivities(false, $processed, $recursive);
                        if (sizeof($childs) > 0) {
                            $result = array_merge($result, $childs);
                        }
                        unset($record);
                    }
                }

            }
        }

        return $result;
    }

    /**
     * Returns the list of all parents node in the grant structure
     *
     * @param bool|false $relatedObjects
     * @param array      $processed
     * @param bool|true  $recursive
     * @return array
     */
    public function getParentsGrants($relatedObjects = false, $processed = array(), $recursive = true)
    {

        //hard limit on how many node will be processed for performance
        $limit = 200;
        if (sizeof($processed) > $limit) {
            return array();
        }

        if ($relatedObjects === false) {
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }

        $result = array();

        $relatedObjects = $this->filterValidRelatedObjects($relatedObjects);

        foreach ($relatedObjects as $relatedObject) {

            $isValidParent = false;

            if ($this->ro->class == 'collection') {
                if ($relatedObject['class'] == 'collection') {
                    //find all explicit isPartOf collection or reverse hasPart
                    $isValidParent = (($relatedObject['relation_type'] == 'isPartOf' && $relatedObject['origin'] == 'EXPLICIT')
                        || ($relatedObject['relation_type'] == 'hasPart' && startsWith($relatedObject['origin'],
                                "REVERSE")));
                } else {
                    if ($relatedObject['class'] == 'activity' || $relatedObject['class'] == 'party') {
                        //find all explicit isOutputOf (activity or party) or reverse hasOutput
                        $isValidParent = (($relatedObject['relation_type'] == 'isOutputOf' && $relatedObject['origin'] == 'EXPLICIT')
                            || ($relatedObject['relation_type'] == 'hasOutput' && startsWith($relatedObject['origin'],
                                    "REVERSE")));
                    }
                }
            } else {
                if ($this->ro->class == 'activity') {
                    if ($relatedObject['class'] == 'activity') {
                        //find all explicit isPartOf activity or reverse hasPart
                        $isValidParent = (($relatedObject['relation_type'] == 'isPartOf' && $relatedObject['origin'] == 'EXPLICIT')
                            || ($relatedObject['relation_type'] == 'hasPart' && startsWith($relatedObject['origin'],
                                    "REVERSE")));
                    } else {
                        if ($relatedObject['class'] == 'party') {
                            //find all explicit isFundedBy activity or reverse funds
                            $isValidParent = (($relatedObject['relation_type'] == 'isFundedBy' && $relatedObject['origin'] == 'EXPLICIT')
                                || ($relatedObject['relation_type'] == 'funds' && startsWith($relatedObject['origin'],"REVERSE"))
                                || ($relatedObject['relation_type'] == 'isFunderOf' && startsWith($relatedObject['origin'],"REVERSE")));
                        }
                    }
                }
            }

            //do not want to check recursively this child again
            $isValidParent = $isValidParent && !in_array($relatedObject['registry_object_id'], $processed);

            //only relates to PUBLISHED records
            $isValidParent = $isValidParent && $relatedObject['status']=='PUBLISHED';

            array_push($processed, $relatedObject['registry_object_id']);

            if ($isValidParent) {
                $result[] = $relatedObject;
                //continue if it's recursive and we haven't reach the fundedBy party yet
                if ($recursive && $relatedObject['class'] != 'party') {
                    $record = $this->_CI->ro->getByID($relatedObject['registry_object_id']);
                    if ($record) {
                        $parents = $record->getParentsGrants(false, $processed, true);
                        if (sizeof($parents) > 0) {
                            $result = array_merge($result, $parents);
                        }
                    }
                }
            }
        }

        //remove unique by registry_object_id
        $temp_array = array();
        foreach ($result as &$v) {
            if (!isset($temp_array[$v['registry_object_id']])) {
                $temp_array[$v['registry_object_id']] =& $v;
            }
        }
        $result = $temp_array;
        return $result;
    }

    /**
     * Returns the structure for grants network
     * Source from SOLR
     * Funder ->funds (activity->hasPart->activity) -> outputs (collection->hasPart->collection)
     *
     * @param array     $processed
     * @param bool|true $recursive
     * @return array
     */
    public function getGrantsStructureSOLR($processed = array(), $recursive = true)
    {
        $this->_CI->load->library('solr');

        //hard coded limit
        $limit = 10;

        $result = array(
            'counts' => 0,
            'childs' => [],
            'has_more' => false
        );

        if ($this->ro->class == 'party') {
            //search for all activity isFundedBy that does not have field isPartOf
            $solrResult = $this->_CI->solr->init()
                ->setOpt('fl', 'id, title, class')
                ->setOpt('rows', $limit)
                ->setOpt('fq', '+relation_grants_isFundedBy_direct:' . $this->ro->id)
                ->executeSearch(true);
        } else {
            if ($this->ro->class == 'activity') {
                //search for all activity that isPartOf and collection that isOutputOf
                $solrResult = $this->_CI->solr->init()
                    ->setOpt('fl', 'id, title, class')
                    ->setOpt('rows', $limit)
                    ->setOpt('fq',
                        'relation_grants_isPartOf_direct:' . $this->ro->id . ' OR relation_grants_isOutputOf_direct:' . $this->ro->id)
                    ->executeSearch(true);
            } else {
                if ($this->ro->class == 'collection') {
                    //search for all collection that isPartOf
                    $solrResult = $this->_CI->solr->init()
                        ->setOpt('fl', 'id, title, class')
                        ->setOpt('rows', $limit)
                        ->setOpt('fq', 'relation_grants_isPartOf_direct:' . $this->ro->id)
                        ->executeSearch(true);
                } else {
                    $solrResult = false;
                }
            }
        }

        if ($solrResult && $solrResult['response']['numFound'] > 0) {
            foreach ($solrResult['response']['docs'] as &$doc) {
                if ($recursive && !in_array($doc['id'], $processed)) {
                    $record = $this->_CI->ro->getByID($doc['id']);
                    $childs = $record->getGrantsStructureSOLR($processed);
                    if (isset($childs['childs'])
                        && is_array($childs['childs'])
                        && sizeof($childs['childs']) > 0) {
                        $doc['childs'] = [
                            'counts' => $childs['counts'],
                            'childs' => $childs['childs'],
                            'has_more' => $childs['counts'] > $limit ? true : false
                        ];
                    }
                }
                $processed[] = $doc['id'];
                $result['childs'][] = $doc;
            }
            $result['counts'] = $solrResult['response']['numFound'];
            $result['has_more'] = ($solrResult['response']['numFound'] > $limit) ? true : false;
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
            $relatedObjects = $this->ro->getAllRelatedObjects();
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
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }
        $result = array();
        $childActivities = $this->getChildActivities($relatedObjects, array(), false);
        foreach ($childActivities as &$childActivity) {
            if (!in_array($childActivity['registry_object_id'], $processed)) {
                array_push($processed, $childActivity['registry_object_id']);
                $record = $this->_CI->ro->getByID($childActivity['registry_object_id']);

                //get data outputs
                $dataOutputs = array();
                $publications = array();
                $related = $record->getAllRelatedObjects();
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
     * @param array|bool|false $childActivities
     * @param array|bool|false $relatedObjects
     * @param bool       $recursive
     * @return array
     */
    public function getDataOutput($childActivities = false, $relatedObjects = false, $recursive = true)
    {
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }
        if (!$childActivities) {
            $childActivities = $this->ro->getChildActivities($relatedObjects, array(), $recursive);
        }

        $result = array();

        $total = sizeof($childActivities);
        $chunkSize = 200;
        $numChunk = ceil(($chunkSize < $total ? ($total / $chunkSize) : 1));

        for ($i = 1; $i <= $numChunk; $i++) {
            $offset = ($i - 1) * $chunkSize;
            $chunkArray = array_slice($childActivities, $offset, $chunkSize);

            $ids = array($this->ro->id);
            foreach ($chunkArray as $activity) {
                if ($activity['registry_object_id'] && !in_array($activity['registry_object_id'], $ids)) {
                    $ids[] = $activity['registry_object_id'];
                }
            }
            if (sizeof($ids) > 0) {
                $ids = '(' . implode(' OR ', $ids) . ')';
                $dataOutputs = $this->getDirectDataOutputSOLR($ids);
                if (sizeof($dataOutputs) > 0) {
                    $result = array_merge($result, $dataOutputs);
                }
            }

            unset($ids);
        }

        //self
        $directOutput = $this->ro->getDirectDataOutput($relatedObjects);
        if (sizeof($directOutput) > 0) {
            $result = array_merge($result, $directOutput);
        }

        //remove duplicates
        $result = array_values(array_map("unserialize", array_unique(array_map("serialize", $result))));

        return $result;
    }

    /**
     * Gets the direct data output from SOLR instead of the database
     * Requires all related objects to be indexed correctly
     * Faster / Possibly less accurate depends on the index status
     *
     * @param bool|false $fromID
     * @return array
     */
    private function getDirectDataOutputSOLR($fromID = false){
        $result = array();
        $ci =& get_instance();
        $ci->load->library('solr');
        $ci->solr->init()->setCore('relations');

        if ($fromID) {
            $ci->solr->setOpt('fq', 'from_id:'.$fromID);
        } else {
            $ci->solr->setOpt('fq', 'from_id:'.$this->ro->id);
        }

        $ci->solr
            ->setOpt('fq', 'to_class:collection')
            ->setOpt('fq', 'relation:(hasOutput OR isOutputOf OR outputs)');

        $solrResult = $ci->solr->executeSearch(true);

        if ($solrResult
            && array_key_exists('response', $solrResult)
            && $solrResult['response']['numFound'] > 0) {
            foreach ($solrResult['response']['docs'] as $doc) {
                $result[] = [
                    'registry_object_id' => $doc['to_id'],
                    'key' => $doc['to_key'],
                    'class' => $doc['to_class'],
                    'title' => $doc['to_title'],
                    'slug' => $doc['to_slug'],
                    'status' => 'PUBLISHED',
                    'relation_type' => $doc['relation'][0],
                    'origin' => $doc['relation_origin'][0],
                    'relation_description' => isset($doc['relation_description']) ? $doc['relation_description'] : "",
                    'type' => $doc['to_type']
                ];
            }
        }
        return $result;
    }

    /**
     * Get relatedObjects that has is a direct data output of this object
     *
     * @param bool|false $relatedObjects
     * @return array
     */
    public function getDirectDataOutput($relatedObjects = false)
    {
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }

        $result = array();

        $relatedObjects = $this->filterValidRelatedObjects($relatedObjects);

        foreach ($relatedObjects as $relatedObject) {

            if (($relatedObject['relation_type'] == 'hasOutput' && $relatedObject['origin'] == 'EXPLICIT')
                || ($relatedObject['relation_type'] == 'isOutputOf' && startsWith($relatedObject['origin'], "REVERSE"))
                || ($relatedObject['relation_type'] == 'outputs' && $relatedObject['origin'] == 'EXPLICIT')
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
            $relatedObjects = $this->ro->getAllRelatedObjects();
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

    /**
     * Get direct trove publication
     *
     * @return array
     */
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
     * Determine if this node is part of a possible grant network
     * Useful to check before commiting to massive recursive grant network generation
     *
     * @param bool|false $relatedObjects
     * @return bool
     */
    public function isValidGrantNetworkNode($relatedObjects = false)
    {
        if (!$relatedObjects) {
            $relatedObjects = $this->ro->getAllRelatedObjects();
        }

        if ($this->ro->class == 'activity') {
            // it has to be a program or a grant to be a valid node in the grant network
            $type = trim(strtolower($this->ro->type));
            if ($type == 'program' || $type == 'grant') {
                return true;
            }
        } elseif ($this->ro->class == 'collection') {
            // it has to be a data output
            foreach ($relatedObjects as $related) {
                if ($related['relation_type'] == 'hasOutput' || $related['relation_type']=='isOutputOf' || $related['relation_type'] == 'outputs') {
                    return true;
                }
            }
        } elseif ($this->ro->class == 'party') {
            // it has to be a funder
            foreach ($relatedObjects as $related) {
                if ($related['relation_type'] == 'funds' || $related['relation_type'] == 'isFundedBy') {
                    return true;
                }
            }
        } else {
            return false;
        }
        return false;
    }

    public function filterValidRelatedObjects($relatedObjects)
    {
        return array_filter($relatedObjects, function($relatedObject){
            if (!$relatedObject) {
                return false;
            }

            if (!is_array($relatedObject)) {
                return false;
            }

            if (!array_key_exists('relation_type', $relatedObject)) {
                return false;
            }

            return true;
        });
    }


    /**
     * Helper method to return the gXPath
     *
     * @return DOMXpath
     */
    public function getGXPath()
    {
        $rifDom = new DOMDocument();

        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->ro->id);
        $data = $record->getCurrentData()->data;

        $rifDom->loadXML($data);
        $gXPath = new DOMXpath($rifDom);
        $gXPath->registerNamespace('ro', RIFCS_NAMESPACE);
        return $gXPath;
    }
}