<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use ANDS\Repository\RegistryObjectsRepository;



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
        $level_data['mandatoryInformation_key'] = ["passed"=>"passed", "message" => "A valid key must be specified for this record"];
        if(strlen($key) == 0 || strlen($key) > 512){
            $passed = "fail";
            $level_data['mandatoryInformation_key'] = ["passed"=>"fail", "message" => "A valid key must be specified for this record"];
        }
        // test for group attribute
        $group = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/@group');
        $group = trim((string)$group[0]);

        $level_data['mandatoryInformation_group'] = ["passed"=>"passed", "message" => "A group must be specified for this record"];
        if(strlen($key) == 0 || strlen($key) > 512){
            $passed = "fail";
            $level_data['mandatoryInformation_group'] = ["passed"=>"fail", "message" => "A group must be specified for this record"];
        }
        // test for type attribute
        $type = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/@type');
        $type = trim((string)$type[0]);
        $level_data['mandatoryInformation_type'] = ["passed"=>"passed", "message" => ucfirst($record->class) . " type must be specified"];
        if(strlen($type) == 0 || strlen($type) > 512){
            $passed = "fail";
            $level_data['mandatoryInformation_type'] = ["passed"=>"fail", "message" => ucfirst($record->class) . " type must be specified"];
        }
        return ["passed"=>$passed, "data"=>$level_data];
    }

    public static function checkLevelTwoQuality($record, $xml){
        $passed = "passed";
        $level_data = [];
        // test for Primary Name
        $primaryName = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:name[@type="primary"]');
        $level_data['name'] = ["passed"=>"passed", "message" => "At least one primary name is required for the ".ucfirst($record->class)." record"];
        if($primaryName == null){
            $passed = "fail";
            $level_data['name'] = ["passed"=>"fail", "message" => "At least one primary name is required for the ".ucfirst($record->class)." record"];
        }
        // test description
        $description = XMLUtil::getElementsByXPath($xml, 'ro:registryObject/node()/ro:description[@type="brief"][string-length(.) > 0]');
        $level_data['description'] = ["passed"=>"passed", "message" => "At least one description (brief and/or full) is required for the ".ucfirst($record->class)];
        if($description == null){
            $passed = "fail";
            $level_data['description'] = ["passed"=>"fail", "message" => "At least one description (brief and/or full) is required for the ".ucfirst($record->class)];
        }

        if($record->class == 'collection'){
            $level_data['relatedObject'] = ["passed"=>"passed", "message" => "The ".ucfirst($record->class)." must be related to at least one Party record"];
            if($record->hasRelatedClass('party') == false){
                $level_data['relatedObject'] = ["passed"=>"fail", "message" => "The ".ucfirst($record->class)." must be related to at least one Party record"];
            }
        }

        return ["passed"=>$passed, "data"=>$level_data];

    }

    public static function checkLevelThreeQuality($record, $xml){
        $passed = "passed";
        $level_data = [];
        $key = XMLUtil::getElementsByName($xml, 'key');
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