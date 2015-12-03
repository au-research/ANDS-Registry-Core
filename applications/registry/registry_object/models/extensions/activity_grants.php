<?php

/**
 * Class Activity_grants_extension
 * To extract all activity only sync values out
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
     * @param bool|false $gXPath
     * @return bool|mixed
     */
    function getFundingAmount($gXPath = false)
    {
        if (!$gXPath) $gXPath = $this->getGXPath();
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
     * @param bool|false $gXPath
     * @return bool|string
     */
    function getFundingScheme($gXPath = false)
    {
        if (!$gXPath) $gXPath = $this->getGXPath();
        $fundingScheme = false;
        foreach ($gXPath->query('//ro:description[@type="fundingScheme"]') as $node) {
            $fundingScheme = strip_tags(html_entity_decode($node->nodeValue));
        }
        return $fundingScheme;
    }

    /**
     * Returns Researchers
     * description[type=researchers]
     * relatedObject[class=party][type=person]
     * todo relatedInfo[type=party][relation=hasPrincipalInvestigator|hasParticipant]
     * @param bool|false $gXPath
     * @param bool|false $relatedObjects
     * @return array
     */
    function getResearchers($gXPath = false, $relatedObjects = false)
    {
        if (!$gXPath) $gXPath = $this->getGXPath();

        $researchers = array();
        foreach ($gXPath->query('//ro:description[@type="researchers"]') as $node) {
            $researchers[] = strip_tags(html_entity_decode($node->nodeValue));
        }

        if (!$relatedObjects) $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        foreach ($relatedObjects as $relatedObject) {
            if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT) {
                if ($relatedObject['class'] == 'party'
                    && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'], 'type'))) == 'person'
                ) {
                    $researchers[] = $relatedObject['title'];
                }
            }
        }

        return $researchers;
    }

    /**
     * Returns all the institutions participating in this research grant
     * relatedObject[relation=isManagedBy|hasParticipant][type=group][class=party]
     * @param bool|false $relatedObjects
     * @return array
     */
    function getInstitutions($relatedObjects = false)
    {
        $institutions = array();
        if (!$relatedObjects) $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        if ($relatedObjects) {
            foreach ($relatedObjects as $relatedObject) {
                if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT) {
                    if ($relatedObject['class'] == 'party'
                        && ($relatedObject['relation_type'] == 'isManagedBy' || $relatedObject['relation_type'] == 'hasParticipant')
                        && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'], 'type'))) == 'group'
                    ) {
                        $institutions[] = $relatedObject['title'];
                    }
                }
            }
        }
        return $institutions;
    }

    /**
     * Returns the Administering Institution
     * relatedObject[relation=isManagedBy][class=party][type!=person]
     * @param bool|false $relatedObjects
     * @return array
     */
    function getAdministeringInstitution($relatedObjects = false)
    {
        $administeringInstitution = array();
        if (!$relatedObjects) $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        if ($relatedObjects) {
            foreach ($relatedObjects as $relatedObject) {
                if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT) {
                    if ($relatedObject['class'] == 'party'
                        && $relatedObject['relation_type'] == 'isManagedBy'
                        && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'], 'type'))) != 'person'
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
     * todo relatedInfo[type=party][relation=isFundedBy]
     * @param bool|false $relatedObjects
     * @return array
     */
    function getFunders($relatedObjects = false)
    {
        $funders = array();
        if (!$relatedObjects) $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        if ($relatedObjects) {
            foreach ($relatedObjects as $relatedObject) {
                if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT) {
                    if ($relatedObject['class'] == 'party'
                        && $relatedObject['relation_type'] == 'isFundedBy'
                        && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'], 'type'))) != 'person'
                    ) {
                        $funders[] = $relatedObject['title'];
                    }
                }
            }
        }
        return $funders;
    }


    /**
     * Return the principal investigator
     * relatedObject[class=party][type=person][relation=hasPrincipalInvestigator]
     * todo relatedInfo[type=party][relation=hasPrincipalInvestigator]
     * @param bool|false $relatedObjects
     * @return array
     */
    function getPrincipalInvestigator($relatedObjects = false)
    {
        $principalInvestigator = array();
        if (!$relatedObjects) $relatedObjects = $this->ro->getAllRelatedObjects(false, false, true);
        if ($relatedObjects) {
            foreach ($relatedObjects as $relatedObject) {
                if (!isset($relatedObject['status']) || $relatedObject['status'] != DRAFT) {
                    if ($relatedObject['class'] == 'party'
                        && $relatedObject['relation_type'] == 'hasPrincipalInvestigator'
                        && strtolower(trim($this->_CI->ro->getAttribute($relatedObject['registry_object_id'], 'type'))) == 'person'
                    ) {
                        $principalInvestigator[] = $relatedObject['title'];
                    }
                }
            }
        }
        return $principalInvestigator;
    }

    /**
     * Returns the Activity Status
     * Works off the earliest and latest dates in existenceDates
     * @param bool|false $xml
     * @return string
     */
    function getActivityStatus($xml = false)
    {
        $activityStatus = 'other';
        if (!$xml) $xml = $this->ro->getSimpleXML();
        foreach ($xml->xpath('//ro:existenceDates') AS $date) {
            $now = time();
            $start = false;
            $end = false;
            if ($date->startDate) {
                if (strlen(trim($date->startDate)) == 4)
                    $date->startDate = "Jan 1, " . $date->startDate;
                $start = strtotime($date->startDate);
            }

            if ($date->endDate) {
                if (strlen(trim($date->endDate)) == 4)
                    $date->endDate = "Dec 31, " . $date->endDate;
                $end = strtotime($date->endDate);
            }

            if ($start || $end) {
                $activityStatus = 'PENDING';
                if (!$start || $start < $now)
                    $activityStatus = 'ACTIVE';
                if ($end && $end < $now)
                    $activityStatus = 'CLOSED';
            }
        }
        return $activityStatus;
    }

    /**
     * Helper method to return the gXPath
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