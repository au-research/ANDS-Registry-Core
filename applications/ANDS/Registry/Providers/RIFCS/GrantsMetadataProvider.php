<?php


namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCSProvider;
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
       return [
            "activity_status" => GrantsMetadataProvider::getActivityStatus($record),
            "funding_amount" => GrantsMetadataProvider::getFundingAmount($record),
            "funding_scheme" => GrantsMetadataProvider::getFundingScheme($record),
            "funding_scheme_search" => GrantsMetadataProvider::getFundingScheme($record),
            "administering_institution" => GrantsMetadataProvider::getAdministeringInstitutions($record),
            "institutions" => GrantsMetadataProvider::getInstitutions($record),
            "funders" => GrantsMetadataProvider::getFunders($record),
            "researchers" => GrantsMetadataProvider::getResearchers($record),
            "principal_investigator" => GrantsMetadataProvider::getPrincipalInvestigator($record)
        ];

    }

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

    public static function getFundingAmount($record)
    {
        $recordData = $record->getCurrentData();
        $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);
        $fundingAmount = false;

        foreach ($registryObjectsElement->xpath('//ro:description[@type="fundingAmount"]') AS $funding) {
            $fundingAmount = (string) $funding;
        }

        return $fundingAmount;
    }

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

    public static function getAdministeringInstitutions($record)
    {
        $administeringInstitution = [];
        $search_params = ['from_id'=>$record->id,'relation_type' => 'isManagedBy', 'to_class' => 'party', 'not_to_type' => 'person'];
        $result = RelationshipSearchService::search($search_params);
        $administeringInstitutions = $result->toArray();
        if(isset($administeringInstitutions['contents']) && count($administeringInstitutions['contents']) > 0 ){
            foreach($administeringInstitutions['contents'] as $party){
                $administeringInstitution[] = $party['to_title'];
            }
        }
        return $administeringInstitution;
    }

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
                    if($include === true) $institutions[] = $party['to_title'];
                }
                //if($include === true) $institutions = $party['to_title'];
            }
        }
        return array_unique($institutions);
    }


    public static function getFunders($record)
    {
        $funders = [];
        $search_params = ['from_id'=>$record->id, 'to_class' => 'party', 'relation_type'=>'isFundedBy'];
        $result = RelationshipSearchService::search($search_params);
        $funderResult = $result->toArray();

        if(isset($funderResult['contents']) && count($funderResult['contents']) > 0 ){
            foreach($funderResult['contents'] as $party){
                 $funders[] = $party['to_title'];
            }
        }
        return array_unique($funders);
    }
    public static function getResearchers($record)
    {
        $researchers = [];
        $recordData = $record->getCurrentData();
        $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);

        foreach ($registryObjectsElement->xpath('//ro:description[@type="researchers"]') AS $researcher) {
            $researchers[] = (string) $researcher;
        }
        $search_params = ['from_id'=>$record->id, 'to_class' => 'party', 'relation_type'=>['hasPrincipalInvestigator','hasParticipant','isAssociatedWith'],'not_to_type' => 'person'];
        $result = RelationshipSearchService::search($search_params);
        $researcherResult = $result->toArray();

        if(isset($researcherResult['contents']) && count($researcherResult['contents']) > 0 ){
           foreach($researcherResult['contents'] as $researcher){
                $researchers[] = $researcher['to_title'];
           }
        }
        return array_unique($researchers);
    }

    public static function getPrincipalInvestigator($record)
    {
        $principalInvestigator = [];
        $search_params = ['from_id'=>$record->id, 'to_class' => 'party', 'relation_type'=>'hasPrincipalInvestigator', 'to_type' => ['party','person']];
        $result = RelationshipSearchService::search($search_params);

        $investigatorResult = $result->toArray();
        if(isset($investigatorResult['contents']) && count($investigatorResult['contents']) > 0 ){
            foreach($investigatorResult['contents'] as $investigator){
                $principalInvestigator[] = $investigator['to_title'] ." || ".$investigator['to_type'] ;
            }
        }
        return array_unique($principalInvestigator);
    }
}