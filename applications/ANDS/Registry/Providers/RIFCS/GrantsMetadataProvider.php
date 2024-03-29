<?php


namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use ANDS\Mycelium\RelationshipSearchService;

class GrantsMetadataProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    public static function get(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    /**
     * Obtain an associative array for the indexable fields
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getIndexableArray(RegistryObject $record)
    {
       $indexableArray = [];

       $indexableArray["activity_status"] = GrantsMetadataProvider::getActivityStatus($record);
       $indexableArray["administering_institution"] = GrantsMetadataProvider::getAdministeringInstitutions($record);
       $indexableArray["institutions"] = GrantsMetadataProvider::getInstitutions($record);
       $indexableArray["funders"] = RelationshipProvider::getFunders($record);
       $indexableArray["researchers"] = GrantsMetadataProvider::getResearchers($record);
       $indexableArray["principal_investigator"] = GrantsMetadataProvider::getPrincipalInvestigator($record);

       // if the following fields do not exist - do not set the array fields
        if(GrantsMetadataProvider::getFundingAmount($record))
            $indexableArray["funding_amount"] = GrantsMetadataProvider::getFundingAmount($record);

        if(GrantsMetadataProvider::getFundingScheme($record)) {
            $indexableArray["funding_scheme"] = GrantsMetadataProvider::getFundingScheme($record);
            $indexableArray["funding_scheme_search"] = GrantsMetadataProvider::getFundingScheme($record);
        }

        if(GrantsMetadataProvider::getEarliestyear($record))
            $indexableArray["earliest_year"] = GrantsMetadataProvider::getEarliestyear($record);

        if(GrantsMetadataProvider::getLatestYear($record))
            $indexableArray["latest_year"] = GrantsMetadataProvider::getLatestYear($record);

        return GrantsMetadataProvider::validateIndexableArray($indexableArray);
    }

    public static function validateIndexableArray(Array $indexableArray){
        foreach($indexableArray as $key=>$attribute){
            if($attribute === false) unset($indexableArray[(string)$key]);
            //TO DO log the unexpected result;
        }
        return $indexableArray;
    }

    /*
     *  Will return a string value of the derived 'activity status' based on rifcs existenceDates elements
     */
    public static function getActivityStatus($record){
        $activityStatus = 'other';
        $recordData = $record->getCurrentData();
        $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);

        foreach ($registryObjectsElement->xpath('//ro:existenceDates') AS $date) {

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

    /*
     *  Will return the value of a rifcs description element of type 'fundingAmount'
     */
    public static function getFundingAmount($record)
    {
        $recordData = $record->getCurrentData();
        $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);
        $fundingAmount = false;

        foreach ($registryObjectsElement->xpath('//ro:description[@type="fundingAmount"]') AS $funding) {
            $res = preg_replace("/[^0-9.]/", "", (string) ($funding));
            $fundingAmount = $res;
        }

        return $fundingAmount;
    }

    /*
     *  Will return the value of a rifcs description element of type 'fundingScheme'
     */
    public static function getFundingScheme($record)
    {
        $recordData = $record->getCurrentData();
        $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);
        $fundingScheme = false;

        foreach ($registryObjectsElement->xpath('//ro:description[@type="fundingScheme"]') AS $funding) {
            $fundingScheme = (string) $funding;
        }

        return $fundingScheme;
    }

    /*
     * Will return a list of  titles of any related party (not a person) with relation_type of 'isManagedBy'
     * - both related objects and related info are returned
     */
    public static function getAdministeringInstitutions($record)
    {

        $administeringInstitution = [];
        $search_params = ['from_id'=>$record->id,'relation_type' => 'isManagedBy', 'to_class' => 'party', 'not_to_type' => 'person'];
        $result = RelationshipSearchService::search($search_params);
        $administeringInstitutions = $result->toArray();
        if(isset($administeringInstitutions['contents']) && count($administeringInstitutions['contents']) > 0 ){
            foreach($administeringInstitutions['contents'] as $party){
                if(isset($party['to_title']))
                $administeringInstitution[] = $party['to_title'];
            }
        }
        return $administeringInstitution;
    }

    /*
     * Will return a list of  titles of any related party (not a person) who has a relationship that is not a funder
     * - only related objects returned (related info will never have a type of group)
     */
    public static function getInstitutions($record)
    {
        $institutions = [];
        $search_params = ['from_id'=>$record->id, 'to_class' => 'party', 'to_type'=>'group'];
        $result = RelationshipSearchService::search($search_params);
        $institutionResult = $result->toArray();

        if(isset($institutionResult['contents']) && count($institutionResult['contents']) > 0 ){

            foreach($institutionResult['contents'] as $party){
                $include = true;
                foreach($party['relations'] as $relations){
                    if($relations['relation_type'] == 'isFundedBy' ||  $relations['relation_type'] == 'isFunderOf'){
                        $include = false;
                    }
                    //to do - determine if we include the institution if only one of multilple relationships are funding
                    if($include === true && isset($party['to_title']))
                        $institutions[] = $party['to_title'];
                }
            }
        }
        return array_unique($institutions);
    }

      /*
     * Will return a list of  titles of any related party  who has a rifcs description element
     *  of type 'researchers', or is a related object or related info of type party with a relation_type of
     * 'hasPrincipalInvestigator','hasParticipant' or 'isAssociatedWith'
     * -  values form related objects, relatedInfo and description[@type="researchers"] are returned
     */
    public static function getResearchers($record)
    {
        $researchers = [];
        $recordData = $record->getCurrentData();
        $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);

        foreach ($registryObjectsElement->xpath('//ro:description[@type="researchers"]') AS $researcher) {
            $researchers[] = (string) $researcher;
        }
        $search_params = ['from_id'=>$record->id, 'to_class' => 'party',
            'relation_type'=>[
                'hasPrincipalInvestigator',
                'hasParticipant',
                'isAssociatedWith',
                'Partner*Investigator',
                'Chie*Investigator',
                'Principal*Investigator'
            ],'not_to_type' => 'group'];
        $result = RelationshipSearchService::search($search_params);
        $researcherResult = $result->toArray();

        if(isset($researcherResult['contents']) && count($researcherResult['contents']) > 0 ){
           foreach($researcherResult['contents'] as $researcher){
               /*** RDA-815
               https://stackoverflow.com/questions/10008366/php-array-key-exists-returning-false-incorrectly
               PHP array_key_exists returning FALSE incorrectly
               */
               if(isset($researcher['to_title']))
                $researchers[] = $researcher['to_title'];
           }
        }
        return array_unique($researchers);
    }

    /*
     * Will return a list of  titles of any related party  or related info of class party and of type 'party' or 'person'
     * with a relation_type of 'hasPrincipalInvestigator'
     * -  related objects and related info  are returned
     */
    public static function getPrincipalInvestigator($record)
    {
        $principalInvestigator = [];
        $search_params = ['from_id'=>$record->id, 'to_class' => 'party',
            'relation_type'=>[
                'hasPrincipalInvestigator',
                'Chief*Investigator',
                'Principal*Investigator',
                'Partner*Investigator'
            ], 'to_type' => ['party','person']];
        $result = RelationshipSearchService::search($search_params);

        $investigatorResult = $result->toArray();
        if(isset($investigatorResult['contents']) && count($investigatorResult['contents']) > 0 ){
            foreach($investigatorResult['contents'] as $investigator){
                if(isset( $investigator['to_title']))
                $principalInvestigator[] = $investigator['to_title'];
            }
        }
        return array_unique($principalInvestigator);
    }

    /**
     * Returns the earliest year in existenceDates
     * @param RegistryObject $record
     * @return bool|string
     */
    public static function getEarliestYear($record)
    {
        $earliestYear = false;
        $recordData = $record->getCurrentData();
        $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);

        foreach ($registryObjectsElement->xpath('//ro:existenceDates') AS $date) {
            if ($date->startDate) {
                if (strlen(trim($date->startDate)) == 4)
                    $date->startDate = "Jan 1, " . $date->startDate;
                $start = strtotime($date->startDate);
                $earliestYear = date("Y", $start);
            }
        }
        return $earliestYear;
    }

    /**
     * Returns the latest year in existenceDates
     * @param RegistryObject $record
     * @return bool|string
     */
    public static function getLatestYear($record)
    {
        $latestYear = false;
        $recordData = $record->getCurrentData();
        $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);

        foreach ($registryObjectsElement->xpath('//ro:existenceDates') AS $date) {
            if ($date->endDate) {
                if (strlen(trim($date->endDate)) == 4)
                    $date->endDate = "Dec 31, " . $date->endDate;
                $end = strtotime($date->endDate);
                $latestYear = date("Y", $end);
            }
        }
        return $latestYear;
    }
}