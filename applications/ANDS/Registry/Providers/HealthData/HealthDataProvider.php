<?php

namespace ANDS\Registry\Providers\HealthData;

use ANDS\Registry\ContentProvider\ANZCTR\ContentProvider;
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


        $anzctr_schema = Schema::get(static::$anzctr_schema_uri);

        $relatedStudies = [];
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
                $relatedStudies[] = $relatedStudy;
                $healthDataset["anzctrPublictitle"] = ContentProvider::getFirst($dom, array('publictitle'));
                $healthDataset["anzctrBriefsummary"] = ContentProvider::getFirst($dom, array('briefsummary'));
                $healthDataset["anzctrConditions"] = ContentProvider::getContent($dom, array('healthcondition'));
                $healthDataset["anzctrConditioncodes"] = ContentProvider::getContent($dom, array('conditioncode'));
                $healthDataset["anzctrStudytype"] = ContentProvider::getContent($dom, array('studytype'));
                $healthDataset["anzctrEthicsapproval"] = ContentProvider::getContent($dom, array('ethicsapproval'));
                $healthDataset["anzctrInclusivecriteria"] = ContentProvider::getContent($dom, array('inclusivecriteria'));
                $healthDataset["anzctrInterventioncode"] = ContentProvider::getContent($dom, array('interventioncode'));
            }
        }

        $healthDataset["relatedStudy"] = $relatedStudies;
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

}