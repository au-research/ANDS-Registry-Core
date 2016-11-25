<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use ANDS\Registry\Providers\RelationshipProvider;




/**
 * Class  QualityMetadataProvider
 * @package ANDS\Registry\Providers
 */
class QualityMetadataProvider
{
    /**
     * Create quality metadata and calculate quality level of a record
     * 
     * 
     *
     * @param RegistryObject $record
     */
   // future
   // private static $attributeKeys = ['quality_level', 'warning_count', 'error_count'];
   // private static $metadataKeys = ['level_html', 'quality_html'];
    // current

    private static $attributeKeys = ['quality_level'];
    private static $metadataKeys = ['level_html'];

    public static function process(RegistryObject $record)
    {
        static::deleteQualityInfo($record);
        
        $quality_report = static::getQualityReport($record);
        static::saveQualityInfo($record, $quality_report);

    }

    public static function deleteQualityInfo($record){
        static::deleteQualityAttributes($record);
        static::deleteQualityMetadata($record);
    }

    public static function deleteQualityAttributes($record){
        foreach(static::$attributeKeys as $key){
            $record->deleteRegistryObjectAttribute($key);
        }
    }


    public static function deleteQualityMetadata($record){
        foreach(static::$metadataKeys as $key){
            $record->deleteRegistryObjectMetadata($key);
        }
    }
    
    
    public static function getQualityReport($record){

        $quality_report = static::generateQualityReport($record);
        // future
        //$quality_report['quality_html'] = static::getQualityHtml($record);

        //$quality_report['warning_count'] = static::getWarningCount($quality_report);;
        //$quality_report['error_count'] = static::getErrorCount($quality_report);
        return $quality_report;
    }

    public static function generateQualityReport($record){
        $recordData = $record->getCurrentData();
        $levels[1] = static::checkLevelOneQuality($record, $recordData->data);
        $levels[2] = static::checkLevelTwoQuality($record, $recordData->data);
        $levels[3] = static::checkLevelTHreeQuality($record, $recordData->data);
        $quality_level = 3;
        foreach($levels as $level=>$report)
        {
            if($quality_level >= $level && $report['passed'] != "passed")
            {
                $quality_level = $level - 1;
            }
        }
        $quality_report['quality_levels_report'] = $levels;
        $quality_report['quality_level'] = $quality_level;

        return $quality_report;
    }


    public static function checkLevelOneQuality($record, $xml){
        $passed = "passed";
        $level_data = [];
        // test for key
        $key = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/ro:key');
        $key = trim((string)$key[0]);
        $level_data['mandatoryInformation_key'] = ["passed"=>"passed", "message" => "A valid key must be specified for this record."];
        if(strlen($key) == 0 || strlen($key) > 512){
            $passed = "fail";
            $level_data['mandatoryInformation_key'] = ["passed"=>"fail", "message" => "A valid key must be specified for this record."];
        }
        // test for group attribute
        $group = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/@group');
        $group = trim((string)$group[0]);

        $level_data['mandatoryInformation_group'] = ["passed"=>"passed", "message" => "A group must be specified for this record."];
        if(strlen($key) == 0 || strlen($key) > 512){
            $passed = "fail";
            $level_data['mandatoryInformation_group'] = ["passed"=>"fail", "message" => "A group must be specified for this record."];
        }
        // test for type attribute
        $type = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/@type');
        $type = trim((string)$type[0]);
        $level_data['mandatoryInformation_type'] = ["passed"=>"passed", "message" => ucfirst($record->class) . " type must be specified."];
        if(strlen($type) == 0 || strlen($type) > 512){
            $passed = "fail";
            $level_data['mandatoryInformation_type'] = ["passed"=>"fail", "message" => ucfirst($record->class) . " type must be specified."];
        }
        return ["passed"=>$passed, "data"=>$level_data];
    }

    public static function checkLevelTwoQuality($record, $xml){
        $passed = "passed";
        $level_data = [];
        // test for Primary Name
        $primaryName = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:name[@type="primary"]');
        $level_data['name'] = ["passed" => "passed", "qa_id" => "REQ_PRIMARY_NAME", "message" => "At least one primary name is required for the ".ucfirst($record->class)." record."];
        if($primaryName == null){
            $passed = "fail";
            $level_data['name'] = ["passed"=>"fail", "qa_id" => "REQ_PRIMARY_NAME", "message" => "At least one primary name is required for the ".ucfirst($record->class)." record."];
        }


        if($record->class == 'party' || $record->class == 'service') {
            $level_data['relatedObject'] = ["passed" => "passed", "qa_id" => "REQ_RELATED_OBJECT_COLLECTION", "message" => "The " . ucfirst($record->class) . " must be related to at least one Collection record."];

            if (RelationshipProvider::hasRelatedClass($record, 'collection') == false) {
                $passed = "fail";
                $level_data['relatedObject'] = ["passed" => "fail", "qa_id" => "REQ_RELATED_OBJECT_COLLECTION", "message" => "The " . ucfirst($record->class) . " must be related to at least one Collection record."];
            }
        }


        if($record->class == 'collection' || $record->class == 'activity') {

            $level_data['relatedObject'] = ["passed" => "passed", "qa_id" => "REQ_RELATED_OBJECT_PARTY", "message" => "The " . ucfirst($record->class) . " must be related to at least one Party record."];

            if (RelationshipProvider::hasRelatedClass($record, 'party') == false) {
                $passed = "fail";
                $level_data['relatedObject'] = ["passed" => "fail", "qa_id" => "REQ_RELATED_OBJECT_PARTY", "message" => "The " . ucfirst($record->class) . " must be related to at least one Party record."];
            }

            // test description
            $brief_description = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:description[@type="brief"][string-length(.) > 0]');
            $full_description = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:description[@type="full"][string-length(.) > 0]');

            $level_data['description'] = ["passed" => "passed", "qa_id" => "REQ_DESCRIPTION_FULL", "message" => "At least one description (brief and/or full) is required for the " . ucfirst($record->class) . "."];
            if ($brief_description == null && $full_description == null) {
                $passed = "fail";
                $level_data['description'] = ["passed" => "fail", "qa_id" => "REQ_DESCRIPTION_FULL", "message" => "At least one description (brief and/or full) is required for the " . ucfirst($record->class) . "."];
            }
        }

        if($record->class == 'collection'){
            $description_right = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:description[@type="rights"][string-length(.) > 0]');
            $description_accessRights = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:description[@type="accessRights"][string-length(.) > 0]');
            $rights = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:rights[string-length(.) > 0]');

            $level_data['description'] = ["passed"=>"passed", "qa_id" => "REQ_RIGHT", "message" => "At least one description of the rights, licences or access rights relating to the ".ucfirst($record->class)." is required."];

            if($description_right == null && $description_accessRights == null && $rights == null){
                $passed = "fail";
                $level_data['description'] = ["passed"=>"fail", "qa_id" => "REQ_RIGHT", "message" => "At least one description of the rights, licences or access rights relating to the ".ucfirst($record->class)." is required."];
            }

            $address = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:location/ro:address/[string-length(.) > 0]');

            $level_data['location'] = ["passed"=>"passed", "qa_id" => "REQ_LOCATION_ADDRESS", "message" => "At least one location address is required for the ".ucfirst($record->class)."."];

            if($address == null){
                $passed = "fail";
                $level_data['location'] = ["passed"=>"fail", "qa_id" => "REQ_LOCATION_ADDRESS", "message" => "At least one location address is required for the ".ucfirst($record->class)."."];
            }
        }

        return ["passed"=>$passed, "data"=>$level_data];

    }

    public static function checkLevelThreeQuality($record, $xml){
        $passed = "passed";
        $level_data = [];




        if($record->class == 'party'){
            $level_data['identifier'] = ["passed" => "passed", "qa_id" => "REC_IDENTIFIER", "message" => "At least one identifier is recommended for the " . ucfirst($record->class) . "."];
            $identifier = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:location/ro:address/[string-length(.) > 0]');

            if ($identifier == null) {
                $passed = "fail";
                $level_data['identifier'] = ["passed" => "fail", "qa_id" => "REC_IDENTIFIER", "message" => "At least one identifier is recommended for the " . ucfirst($record->class) . "."];
            }

            $level_data['relatedObject'] = ["passed"=>"passed", "qa_id" => "REC_RELATED_OBJECT_ACTIVITY", "message" => "It is recommended that the ".ucfirst($record->class)." be related to at least one Activity record."];
            if(RelationshipProvider::hasRelatedClass($record, 'activity') == false){
                $passed = "fail";
                $level_data['relatedObject'] = ["passed"=>"fail", "qa_id" => "REC_RELATED_OBJECT_ACTIVITY", "message" => "It is recommended that the ".ucfirst($record->class)." be related to at least one Activity record."];
            }
        }

        if($record->class == 'activity'){
            $level_data['relatedObject'] = ["passed"=>"passed", "qa_id" => "REC_RELATED_OBJECT_COLLECTION", "message" => "The ".ucfirst($record->class)." must be related to at least one Collection record if available."];
            if(RelationshipProvider::hasRelatedClass($record, 'activity') == false){
                $passed = "fail";
                $level_data['relatedObject'] = ["passed"=>"fail", "qa_id" => "REC_RELATED_OBJECT_COLLECTION", "message" => "The ".ucfirst($record->class)." must be related to at least one Collection record if available."];
            }
        }

        if($record->class == 'collection'){

            $level_data['identifier'] = ["passed" => "passed", "qa_id" => "REC_IDENTIFIER", "message" => "At least one identifier is recommended for the " . ucfirst($record->class) . "."];
            $identifier = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:location/ro:address/[string-length(.) > 0]');

            if ($identifier == null) {
                $passed = "fail";
                $level_data['identifier'] = ["passed" => "fail", "qa_id" => "REC_IDENTIFIER", "message" => "At least one identifier is recommended for the " . ucfirst($record->class) . "."];
            }

            $level_data['relatedObject'] = ["passed"=>"passed", "qa_id" => "REC_RELATED_OBJECT_ACTIVITY", "message" => "The ".ucfirst($record->class). " must be related to at least one Activity record where available."];
            if(RelationshipProvider::hasRelatedClass($record, 'activity') == false){
                $passed = "fail";
                $level_data['relatedObject'] = ["passed"=>"fail", "qa_id" => "REC_RELATED_OBJECT_ACTIVITY", "message" => "The ".ucfirst($record->class). " must be related to at least one Activity record where available."];
            }

            $level_data['spatial_coverage'] = ["passed"=>"passed", "qa_id" => "REC_SPATIAL_COVERAGE", "message" => "At least one spatial coverage for the ".ucfirst($record->class)." is recommended."];
            $spatial_coverage = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:coverage/ro:spatial]');

            if($spatial_coverage == null){
                $passed = "fail";
                $level_data['spatial_coverage'] = ["passed"=>"fail", "qa_id" => "REC_SPATIAL_COVERAGE", "message" => "At least one spatial coverage for the ".ucfirst($record->class)." is recommended."];
            }

            $level_data['temporal_coverage'] = ["passed"=>"passed", "qa_id" => "REC_TEMPORAL_COVERAGE", "message" => "At least one temporal coverage entry for the ".ucfirst($record->class)." is recommended."];
            $temporal_coverage = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:coverage/ro:temporal/ro:date[@type="dateFrom" or @type="dateTo"]');

            if($temporal_coverage == null){
                $passed = "fail";
                $level_data['temporal_coverage'] = ["passed"=>"fail", "qa_id" => "REC_TEMPORAL_COVERAGE", "message" => "At least one temporal coverage entry for the ".ucfirst($record->class)." is recommended."];
            }

            $level_data['citationInfo'] = ["passed"=>"passed", "qa_id" => "REC_CITATION", "message" => "Citation data for the ".ucfirst($record->class)." is recommended."];
            $citationInfo = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:citationInfo');

            if($citationInfo == null){
                $passed = "fail";
                $level_data['citationInfo'] = ["passed"=>"fail", "qa_id" => "REC_CITATION", "message" => "Citation data for the ".ucfirst($record->class)." is recommended."];
            }

            $level_data['dates'] = ["passed"=>"passed", "qa_id" => "REC_DATES", "message" => "At least one dates element is recommended for the  ".ucfirst($record->class)."."];
            $dates = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:dates/ro:date');

            if($dates == null){
                $passed = "fail";
                $level_data['dates'] = ["passed"=>"fail", "qa_id" => "REC_DATES", "message" => "At least one dates element is recommended for the  ".ucfirst($record->class)."."];
            }

        }

        if($record->class == 'service'){
            $address = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:location/ro:address/ro:electronic[string-length(.) > 0]');

            $level_data['location'] = ["passed"=>"passed", "qa_id" => "REC_LOCATION_ADDRESS_ELECTRONIC", "message" => "At least one electronic address is required for the ".ucfirst($record->class)." if available."];

            if($address == null){
                $passed = "fail";
                $level_data['location'] = ["passed"=>"fail", "qa_id" => "REC_LOCATION_ADDRESS_ELECTRONIC", "message" => "At least one electronic address is required for the ".ucfirst($record->class)." if available."];
            }

            $accessPolicy = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:accessPolicy[string-length(.) > 0]');

            $level_data['accessPolicy'] = ["passed"=>"passed", "qa_id" => "REC_ACCESS_POLICY", "message" => "At least one Access Policy URL is recommended for the ".ucfirst($record->class)." record."];

            if($accessPolicy == null){
                $passed = "fail";
                $level_data['accessPolicy'] = ["passed"=>"fail", "qa_id" => "REC_ACCESS_POLICY", "message" => "At least one Access Policy URL is recommended for the ".ucfirst($record->class)." record."];
            }

            $level_data['relatedObject'] = ["passed"=>"passed", "qa_id" => "REC_RELATED_OBJECT_PARTY", "message" => "It is recommended that the ".ucfirst($record->class)." be related to at least one Party record."];
            if(RelationshipProvider::hasRelatedClass($record, 'party') == false){
                $passed = "fail";
                $level_data['relatedObject'] = ["passed"=>"fail", "qa_id" => "REC_RELATED_OBJECT_PARTY", "message" => "It is recommended that the ".ucfirst($record->class)." be related to at least one Party record."];
            }
        }

        if($record->class == 'party' || $record->class == 'service'){
            // test description
            $brief_description = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:description[@type="brief"][string-length(.) > 0]');
            $full_description = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:description[@type="full"][string-length(.) > 0]');

            $level_data['description'] = ["passed"=>"passed", "qa_id" => "REC_DESCRIPTION_FULL", "message" => "At least one description (brief and/or full) is recommended for the ".ucfirst($record->class)."."];
            if($brief_description == null && $full_description == null){
                $passed = "fail";
                $level_data['description'] = ["passed"=>"fail", "qa_id" => "REC_DESCRIPTION_FULL", "message" => "At least one description (brief and/or full) is recommended for the ".ucfirst($record->class)."."];
            }
        }


        if($record->class == 'party' || $record->class == 'activity'){
            $address = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:location/ro:address/[string-length(.) > 0]');

            $level_data['location'] = ["passed"=>"passed", "qa_id" => "REC_LOCATION_ADDRESS", "message" => "At least one location address is recommended for the ".ucfirst($record->class)."."];

            if($address == null){
                $passed = "fail";
                $level_data['location'] = ["passed"=>"fail", "qa_id" => "REC_LOCATION_ADDRESS", "message" => "At least one location address is recommended for the ".ucfirst($record->class)."."];
            }

            $level_data['existenceDates'] = ["passed"=>"passed", "qa_id" => "REC_EXISTENCEDATE", "message" => "Existence dates are recommended for the ".ucfirst($record->class)."."];
            $existenceDates = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:existenceDates');

            if($existenceDates == null){
                $passed = "fail";
                $level_data['existenceDates'] = ["passed"=>"fail", "qa_id" => "REC_EXISTENCEDATE", "message" => "Existence dates are recommended for the ".ucfirst($record->class)."."];
            }
        }

        if($record->class == 'party' || $record->class == 'activity' || $record->class == 'collection') {
            $level_data['subject'] = ["passed" => "passed", "qa_id" => "REC_SUBJECT", "message" => "At least one subject (e.g. anzsrc-for code) is recommended for the " . ucfirst($record->class) . " is required."];
            $subject = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:subject[string-length(.) > 0]');

            if ($subject == null) {
                $passed = "fail";
                $level_data['subject'] = ["passed" => "fail", "qa_id" => "REC_SUBJECT", "message" => "At least one subject (e.g. anzsrc-for code) is recommended for the " . ucfirst($record->class) . " is required."];
            }
        }

        return ["passed"=>$passed, "data"=>$level_data];
    }


    public static function saveQualityInfo($record, $quality_report){

        var_dump($quality_report);
        foreach($quality_report as $key=>$value){
            if(in_array($key, static::$attributeKeys)){
                $record->setRegistryObjectAttribute($key, $value);
            }
            
            elseif(in_array($key , static::$metadataKeys)){
                $record->setRegistryObjectMetadata($key, $value);
            }
        }
        return $quality_report;
    }
   
}