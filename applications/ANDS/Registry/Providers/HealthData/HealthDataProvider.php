<?php

namespace ANDS\Registry\Providers\HealthData;

use ANDS\Mycelium\RelationshipSearchService;
use ANDS\Registry\ContentProvider\ANZCTR\ContentProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\DescriptionProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Registry\Providers\RIFCS\JsonLDProvider;
use ANDS\Registry\Providers\RIFCS\SubjectProvider;
use ANDS\Registry\Schema;
use ANDS\Registry\Versions;
use ANDS\RegistryObject;
use ANDS\RegistryObject\RegistryObjectVersion;
use ANDS\Util\XMLUtil;
use DOMDocument;

class HealthDataProvider
{

    private static $doi_schema_uri = 'http://datacite.org/schema/kernel-4';
    private static $anzctr_schema_uri = 'https://anzctr_org.au';

    public static function get(RegistryObject $record)
    {

        $healthDataset = [];

        $healthDataset["id"] = $record->id;
        $healthDataset["type"] = $record->type;
        $healthDataset["title"] = $record->title;
        $healthDataset["identifiers"][] = ["value" => $record->key];
        $healthDataset["logo"] = "https://marketing-pages.anu.edu.au/_anu/4/images/logos/2x_anu_logo_small.svg";
        $descriptions = DescriptionProvider::get($record);
        $healthDataset["description"] = $descriptions["primary_description"];
        $healthDataset["orgTitle"] = self::getPublisher($record);
        $healthDataset["distributor"] = self::getDistributor($record);
        $healthDataset["contact"] = "services@ardc.edu.au";
        $subjectIndex = SubjectProvider::getIndexableArray($record);
        $healthDataset['subjects'] = $subjectIndex['subject_value_resolved'];

        $doi_schema = Schema::get(static::$doi_schema_uri);

        $altVersionsIDs = RegistryObjectVersion::where('registry_object_id', $record->id)->get()->pluck('version_id')->toArray();

        $relatedDatasets = [];
        if (count($altVersionsIDs) > 0) {
            $datacite_metadata = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $doi_schema->id)->first();
            if($datacite_metadata != null){

                $dom = new DOMDocument;
                $dom->loadXML($datacite_metadata->data);
                $identifier = [];
                $identifier["type"] = "DOI";
                $identifier["value"] =  $dom->getElementsByTagName("identifier")->item(0)->nodeValue;
                $relatedDataset["title"] = $dom->getElementsByTagName("title")->item(0)->nodeValue;
                $relatedDataset['identifiers'][] = $identifier;
                $relatedDatasets[] = $relatedDataset;
            }

        }
        $healthDataset["relatedDatasets"] = $relatedDatasets;
        $healthDataset["relationships"] = self::getRelationships($record->id, $record->group);

        $anzctr_schema = Schema::get(static::$anzctr_schema_uri);

        if (count($altVersionsIDs) > 0) {
            $trial_metadata = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $anzctr_schema->id)->first();
            if($trial_metadata != null){
                $dom = new DOMDocument;
                $dom->loadXML($trial_metadata->data);
                $identifier = [];
                $identifier["type"] = "ANZCTR";
                $identifier["value"] =  $dom->getElementsByTagName("actrn")->item(0)->nodeValue;
                $relatedStudy["title"] = $dom->getElementsByTagName("publictitle")->item(0)->nodeValue;
                $relatedStudy['identifiers'][] = $identifier;
                $relatedStudy["briefSummary"] = ContentProvider::getFirst($dom, array('briefsummary'));
                $relatedStudy["conditions"] = ContentProvider::getContent($dom, array('healthcondition'));
                $relatedStudy["conditionCodes"] = ContentProvider::getContent($dom, array('conditioncode'));
                $relatedStudy["studyType"] = ContentProvider::getContent($dom, array('studytype'));
                $relatedStudy["ethicsApproval"] = ContentProvider::getContent($dom, array('ethicsapproval'));
                $relatedStudy["inclusiveCriteria"] = ContentProvider::getContent($dom, array('inclusivecriteria'));
                $relatedStudy["interventionCode"] = ContentProvider::getContent($dom, array('interventioncode'));
                $healthDataset["relatedStudy"] =  $relatedStudy;
            }
        }


        return $healthDataset;
    }

    public static function getPublisher(RegistryObject $record){
        $xml = $record->getCurrentData()->data;
        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:publisher') AS $publisher) {
            $publishers = (string)$publisher;
            return $publishers;
        };
        $publishers = $record->group;
        return $publishers;
    }

    public static function getRelationships($recordId, $group)
    {
        return [
            'data' => self::getRelatedData($recordId, $group),
            'software' => self::getRelatedSoftware($recordId, $group),
            'publications' => self::getRelatedPublication($recordId, $group),
            'programs' => self::getRelatedPrograms($recordId, $group),
            'grants_projects' => self::getRelatedGrantsProjects($recordId, $group),
            'services' => self::getRelatedService($recordId, $group),
            'websites' => self::getRelatedWebsites($recordId, $group),
            'researchers' => self::getRelatedResearchers($recordId, $group),
            'organisations' => self::getRelatedOrganisations($recordId)
        ];
    }

    /**
     * Obtain related data from SOLR
     * @return array
     */
    private static function getRelatedData($recordId, $group) {

        $result = RelationshipSearchService::search([
            'from_id' => $recordId,
            'to_class' => 'collection',
            'not_to_type' => 'software',
            'to_title' => '*'
        ], ['boost_to_group' => $group ,'rows' => 5]);
        return $result->toArray();
    }

    /**
     * Obtain related software from SOLR
     * @return array
     */
    private static function getRelatedSoftware($recordId, $group) {

        $result = RelationshipSearchService::search([
            'from_id' => $recordId,
            'to_class' => 'collection',
            'to_type' => 'software',
            'to_title' => '*'
        ], ['boost_to_group' => $group , 'rows' => 5]);
        return $result->toArray();
    }

    /**
     * Obtain related programs from SOLR
     * @return array
     */
    private static function getRelatedPrograms($recordId, $group) {
        $result = RelationshipSearchService::search([
            'from_id' => $recordId,
            'to_class' => 'activity',
            'to_type' => 'program',
            'to_title' => '*'
        ], ['boost_to_group' => $group , 'rows' => 5]);

        $programs = $result->toArray();

        //obtaining to_funder for each of the program
        foreach($programs['contents'] as $key=>$grant){
            // if the grant is not a related Object
            if ($grant['to_identifier_type'] === "ro:id") {
                $result2 = RelationshipSearchService::search([
                    'from_id' => $grant["to_identifier"],
                    'to_class' => 'party',
                    'relation_type' =>  ['isFunderOf', 'isFundedBy']
                ], ['rows' => 1]);
                $funded_by = $result2->toArray();
                // the funder's title is the to_title
                if (array_key_exists('contents', $funded_by) && count($funded_by['contents']) > 0) {
                    $programs['contents'][$key]["to_funder"] = $funded_by['contents'][0]["to_title"];
                }
            }else{ // RDA-758 it should still have a funder but we need to search from their end
                $result2 = RelationshipSearchService::search([
                    'to_identifier' => $grant["to_identifier"],
                    'from_class' => 'party',
                    'relation_type' =>  ['isFunderOf', 'isFundedBy']
                ], ['rows' => 1]);
                $funded_by = $result2->toArray();
                // the funder's title is the from_title
                if (array_key_exists('contents', $funded_by) && count($funded_by['contents']) > 0) {
                    $programs['contents'][$key]["to_funder"] = $funded_by['contents'][0]["from_title"];
                }
            }
        }
        return $programs ;
    }

    /**
     * Obtain related activity that are grants or projects from SOLR
     * @return array
     */
    private static function getRelatedGrantsProjects($recordId, $group) {

        $result = RelationshipSearchService::search([
            'from_id' => $recordId,
            'to_class' => 'activity',
            'to_title' => '*',
            'not_to_type' => 'program'
        ], ['boost_to_group' => $group, 'rows' => 5]);
        $grants_projects = $result->toArray();

        foreach($grants_projects['contents'] as $key=>$grant){
            if($grant["to_identifier_type"] === "ro:id"){
                $result2 = RelationshipSearchService::search([
                    'from_id' => $grant["to_identifier"],
                    'to_class' => 'party',
                    'relation_type' =>  ['isFunderOf', 'isFundedBy']
                ], ['rows' => 1]);
                $funded_by = $result2->toArray();
                // the funder's title is the to_title
                if(isset($funded_by['contents']) && count($funded_by['contents'])>0){
                    $grants_projects['contents'][$key]["to_funder"] = $funded_by['contents'][0]["to_title"];
                }
            }else{// RDA-758 it should still have a funder but we need to search from their end
                $result2 = RelationshipSearchService::search([
                    'to_identifier' => $grant["to_identifier"],
                    'from_class' => 'party',
                    'relation_type' =>  ['isFunderOf', 'isFundedBy']
                ], ['rows' => 1]);
                $funded_by = $result2->toArray();
                // the funder's title is the from_title
                if(isset($funded_by['contents']) && count($funded_by['contents'])>0){
                    $grants_projects['contents'][$key]["to_funder"] = $funded_by['contents'][0]["from_title"];
                }
            }
        }
        return $grants_projects ;
    }

    /**
     * Obtain related publications from SOLR
     * @return array
     */
    private static function getRelatedPublication($recordId, $group) {

        $result = RelationshipSearchService::search([
            'from_id' => $recordId,
            'to_class' => 'publication'
        ], ['boost_to_group' => $group, 'rows' =>100]);
        return $result->toArray();
    }

    /**
     * Obtain related services from SOLR
     * @return array
     */
    private static function getRelatedService($recordId, $group) {

        $result = RelationshipSearchService::search([
            'from_id' => $recordId,
            'to_class' => 'service',
            'to_title' => '*'
        ], ['boost_to_group' => $group, 'rows' => 5]);
        return $result->toArray();
    }

    /**
     * Obtain related websites from SOLR
     * @return array
     */
    private static function getRelatedWebsites($recordId, $group) {

        $result = RelationshipSearchService::search([
            'from_id' => $recordId,
            'to_class' => 'website'
        ], ['boost_to_group' => $group ,'rows' =>100]);
        return $result->toArray();
    }

    /**
     * Obtain related researchers from SOLR
     * relationships where there's a hasPrincipalInvestigator edge is ranked higher via boosted query
     * @return array
     */
    // RDA-627 make boost relation_type an array and boost decrease by the order in the array
    private static function getRelatedResearchers($recordId, $group) {

        $result = RelationshipSearchService::search([
            'from_id' => $recordId,
            'to_class' => 'party',
            'not_to_type' => 'group',
            'to_title' => '*',
        ], ['boost_to_group' => $group ,'boost_relation_type' =>
            ['Principal Investigator','hasPrincipalInvestigator','Chief Investigator'] ,
            'rows' => 5, 'sort' => 'score desc, to_title asc']);
        return $result->toArray();

    }

    /**
     * Obtain related organisations from SOLR
     * @return array
     */
    private static function getRelatedOrganisations($recordId) {

        $result = RelationshipSearchService::search([
            'from_id' => $recordId,
            'to_class' => 'party',
            'to_type' => 'group',
            'to_title' => '*'
        ], ['rows' => 5]);
        return $result->toArray();
    }

    /**
     * @param $record
     * @return void
     *
     */
    private static function getDistributor($record){

    }

}