<?php


namespace ANDS\Registry\Providers\Quality;


use ANDS\Registry\Providers\Quality\Exception\MissingDescriptionForCollection;
use ANDS\Registry\Providers\Quality\Exception\MissingGroup;
use ANDS\Registry\Providers\Quality\Exception\MissingOriginatingSource;
use ANDS\Registry\Providers\Quality\Exception\MissingTitle;
use ANDS\Registry\Providers\Quality\Exception\MissingType;
use ANDS\Registry\Providers\Quality\Types;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Util\Config;
use ANDS\Util\XMLUtil;

/**
 * Class  QualityMetadataProvider
 * @package ANDS\Registry\Providers
 */
class QualityMetadataProvider
{

    // future
    private static $attributeKeys = ['quality_level', 'warning_count', 'error_count'];
    // private static $metadataKeys = ['level_html', 'quality_html'];
    // current

    //private static $attributeKeys = ['quality_level'];
    private static $metadataKeys = ['quality_info', 'quality_html'];

    /**
     * Create quality metadata and calculate quality level of a record
     *
     * @param RegistryObject $record
     */
    public static function process(RegistryObject $record)
    {
        static::deleteQualityInfo($record);
        $quality_report = static::getQualityReport($record);
        static::saveQualityInfo($record, $quality_report);
    }

    /**
     * @param $xml
     * @return bool
     * @throws \Exception
     */
    public static function validate($xml)
    {
        $xml = XMLUtil::ensureWrappingRegistryObjects($xml);
        $sm = XMLUtil::getSimpleXMLFromString($xml);
        $class = XMLUtil::getRegistryObjectClass($xml, $sm);

        // originatingSource is required
        $originatingSource = (string) $sm->xpath('//ro:originatingSource')[0];
        if (!trim($originatingSource)) {
            throw new MissingOriginatingSource("Registry Object 'originatingSource' must have a value");
        }

        // group is required
        $group = (string) $sm->xpath("//ro:registryObject")[0]['group'];
        if (!trim($group)) {
            throw new MissingGroup("Registry Object '@group' must have a value");
        }

        // must have a title (provided by names)
        $names = $sm->xpath('//ro:name');
        if (count($names) === 0) {
            throw new MissingTitle("Registry Object 'name' must have a value");
        }

        // if there's a name, must have a namePart
        $nameParts = $sm->xpath('//ro:name/ro:namePart');
        if (count($nameParts) === 0) {
            throw new MissingTitle("Registry Object 'name' must have a value");
        }

        // nameParts must have a value
        $strings = collect($nameParts)->map(function ($value) {
            return (string)$value;
        })->implode('');

        if (!trim($strings)) {
            throw new MissingTitle("Registry Object 'name' must have a value");
        }

        // (collection only) must have a description
        if ($class === "collection") {
            $descriptions = $sm->xpath("/ro:registryObjects/ro:registryObject/ro:collection/ro:description");
            if (count($descriptions) === 0) {
                throw new MissingDescriptionForCollection("Collection must have a description");
            }

            $strings = collect($descriptions)->map(function ($value) {
                return (string)$value;
            })->implode('');

            if (!trim($strings)) {
                throw new MissingDescriptionForCollection("Collection must have a description");
            }
        }

        // type must not be empty
        $type = (string) $sm->xpath("//ro:{$class}")[0]['type'];
        if (!trim($type)) {
            throw new MissingType("Registry Object '@type' must have a value");
        }

        return true;
    }

    /**
     * @param RegistryObject $record
     * @return array
     * @throws \Exception
     */
    public static function getMetadataReport(RegistryObject $record)
    {
        $checks = self::getChecksForClass($record->class);

        return self::reports($record, $checks);
    }

    /**
     * @param $class
     * @return array
     * @throws \Exception
     */
    public static function getChecksForClass($class)
    {
        $config = Config::get('quality.checks');

        return $config[$class];
    }

    /**
     * @param RegistryObject $record
     * @param array $checks
     * @return array
     * @throws \Exception
     */
    public static function reports(RegistryObject $record, array $checks)
    {
        $xml = $record->getCurrentData()->data;
        $simpleXML = XMLUtil::getSimpleXMLFromString($xml);

        $report = [];
        foreach ($checks as $checkClassName) {

            /** @var Types\CheckType $check */
            $check = new $checkClassName($record, $simpleXML);
            $result = $check->toArray();

            $report[] = $result;
        }

        return $report;
    }

    /**
     * Delete all qualityInfo for a record
     *
     * @param $record
     */
    public static function deleteQualityInfo($record)
    {
        static::deleteQualityAttributes($record);
        static::deleteQualityMetadata($record);
    }

    /**
     * Delete attribute
     *
     * @param $record
     */
    public static function deleteQualityAttributes($record)
    {
        foreach (static::$attributeKeys as $key) {
            $record->deleteRegistryObjectAttribute($key);
        }
    }


    /**
     * Delete metadata
     *
     * @param $record
     */
    public static function deleteQualityMetadata($record)
    {
        foreach (static::$metadataKeys as $key) {
            $record->deleteRegistryObjectMetadata($key);
        }
    }

    /**
     * Returns the HTML presentation of a quality report
     * after process
     *
     * @param RegistryObject $record
     * @return string
     */
    public static function getQualityReportHTML(RegistryObject $record)
    {
        $quality_info = $record->getRegistryObjectMetadata('quality_info');

        if ($quality_info) {
            return static::formatQualityInfo($record, $quality_info->value);
        } else {
            static::process($record);
            $quality_info = $record->getRegistryObjectMetadata('quality_info');
            return static::formatQualityInfo($record, $quality_info->value);
        }
    }


    /**
     * Return the quality report generation
     *
     * @param $record
     * @return mixed
     */
    public static function getQualityReport($record)
    {
        $quality_report = static::generateQualityReport($record);
        return $quality_report;
    }

    /**
     * Primary function to generate quality report
     *
     * @param $record
     * @return mixed
     */
    public static function generateQualityReport($record)
    {
        $recordData = $record->getCurrentData();

        $simpleXML = XMLUtil::getSimpleXMLFromString($recordData->data);

        $levels[1] = static::checkLevelOneQuality($record, $simpleXML);
        $levels[2] = static::checkLevelTwoQuality($record, $simpleXML);
        $levels[3] = static::checkLevelThreeQuality($record, $simpleXML);
        $quality_level = 3;
        foreach ($levels as $level => $report) {
            if ($quality_level >= $level && $report['passed'] != "passed") {
                $quality_level = $level - 1;
            }
        }
        $quality_report['quality_info'] = json_encode($levels);
        $quality_report['quality_level'] = $quality_level;

        return $quality_report;
    }


    /**
     * Returns the quality level 1 report
     *
     * @param $record
     * @param $xml
     * @return array
     */
    public static function checkLevelOneQuality($record, $xml)
    {
        $passed = "passed";
        $level_data = [];

        // test for key
        $key = XMLUtil::getElementsByXPathFromSXML(
            $xml, 'ro:registryObject/ro:key'
        );

        $key = trim((string)$key[0]);
        $level_data['mandatoryInformation_key'] = [
            "passed" => "passed",
            "message" => "A valid key must be specified for this record."
        ];
        if (strlen($key) == 0 || strlen($key) > 512) {
            $passed = "fail";
            $level_data['mandatoryInformation_key'] = [
                "passed" => "fail",
                "message" => "A valid key must be specified for this record."
            ];
        }
        // test for group attribute
        $group = XMLUtil::getElementsByXPathFromSXML(
            $xml, 'ro:registryObject/@group'
        );
        $group = trim((string)$group[0]);

        $level_data['mandatoryInformation_group'] = [
            "passed" => "passed",
            "message" => "A group must be specified for this record."
        ];

        if (strlen($key) == 0 || strlen($key) > 512) {
            $passed = "fail";
            $level_data['mandatoryInformation_group'] = [
                "passed" => "fail",
                "message" => "A group must be specified for this record."
            ];
        }
        // test for type attribute
        $type = XMLUtil::getElementsByXPathFromSXML(
            $xml, 'ro:registryObject/ro:' . $record->class . '/@type'
        );
        $type = trim((string)$type[0]);
        $level_data['mandatoryInformation_type'] = [
            "passed" => "passed",
            "message" => ucfirst($record->class) . " type must be specified."
        ];
        if (strlen($type) == 0 || strlen($type) > 512) {
            $passed = "fail";
            $level_data['mandatoryInformation_type'] = [
                "passed" => "fail",
                "message" => ucfirst($record->class) . " type must be specified."
            ];
        }
        return ["passed" => $passed, "data" => $level_data];
    }

    /**
     * Returns quality level 2 report
     *
     * @param $record
     * @param $xml
     * @return array
     */
    public static function checkLevelTwoQuality($record, $xml)
    {
        $passed = "passed";
        $level_data = [];
        // test for Primary Name
        $primaryName = XMLUtil::getElementsByXPathFromSXML(
            $xml,
            'ro:registryObject/ro:' . $record->class . '/ro:name[@type="primary"]'
        );
        $level_data['name'] = [
            "passed" => "passed",
            "qa_id" => "REQ_PRIMARY_NAME",
            "message" => "At least one primary name is required for the " . ucfirst($record->class) . " record."
        ];

        if ($primaryName == null) {
            $passed = "fail";
            $level_data['name'] = [
                "passed" => "fail",
                "qa_id" => "REQ_PRIMARY_NAME",
                "message" => "At least one primary name is required for the " . ucfirst($record->class) . " record."
            ];
        }

        if ($record->class == 'party' || $record->class == 'service') {
            $level_data['relatedObject'] = [
                "passed" => "passed",
                "qa_id" => "REQ_RELATED_OBJECT_COLLECTION",
                "message" => "The " . ucfirst($record->class) . " must be related to at least one Collection record."
            ];

            if (RelationshipProvider::hasRelatedClass($record,'collection') == false) {
                $passed = "fail";
                $level_data['relatedObject'] = [
                    "passed" => "fail",
                    "qa_id" => "REQ_RELATED_OBJECT_COLLECTION",
                    "message" => "The " . ucfirst($record->class) . " must be related to at least one Collection record."
                ];
            }
        }


        if ($record->class == 'collection' || $record->class == 'activity') {

            $level_data['relatedObject'] = [
                "passed" => "passed",
                "qa_id" => "REQ_RELATED_OBJECT_PARTY",
                "message" => "The " . ucfirst($record->class) . " must be related to at least one Party record."
            ];

            if (RelationshipProvider::hasRelatedClass($record,'party') == false) {
                $passed = "fail";
                $level_data['relatedObject'] = [
                    "passed" => "fail",
                    "qa_id" => "REQ_RELATED_OBJECT_PARTY",
                    "message" => "The " . ucfirst($record->class) . " must be related to at least one Party record."
                ];
            }

            // test description
            $brief_description = XMLUtil::getElementsByXPathFromSXML(
                $xml,
                'ro:registryObject/ro:' . $record->class .'/ro:description[@type="brief"][string-length(.) > 0]'
            );
            $full_description = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:description[@type="full"][string-length(.) > 0]');

            $level_data['description'] = [
                "passed" => "passed",
                "qa_id" => "REQ_DESCRIPTION_FULL",
                "message" => "At least one description (brief and/or full) is required for the " . ucfirst($record->class) . "."
            ];
            if ($brief_description == null && $full_description == null) {
                $passed = "fail";
                $level_data['description'] = [
                    "passed" => "fail",
                    "qa_id" => "REQ_DESCRIPTION_FULL",
                    "message" => "At least one description (brief and/or full) is required for the " . ucfirst($record->class) . "."
                ];
            }
        }

        if ($record->class == 'collection') {
            $description_right = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:description[@type="rights"][string-length(.) > 0]');
            $description_accessRights = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:description[@type="accessRights"][string-length(.) > 0]');
            $rights = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:rights[string-length(.) > 0]');

            $level_data['description'] = [
                "passed" => "passed",
                "qa_id" => "REQ_RIGHT",
                "message" => "At least one description of the rights, licences or access rights relating to the " . ucfirst($record->class) . " is required."
            ];

            if ($description_right == null && $description_accessRights == null && $rights == null) {
                $passed = "fail";
                $level_data['description'] = [
                    "passed" => "fail",
                    "qa_id" => "REQ_RIGHT",
                    "message" => "At least one description of the rights, licences or access rights relating to the " . ucfirst($record->class) . " is required."
                ];
            }

            $address = XMLUtil::getElementsByXPathFromSXML(
                $xml,
                'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address'
            );

            $level_data['location'] = [
                "passed" => "passed",
                "qa_id" => "REQ_LOCATION_ADDRESS",
                "message" => "At least one location address is required for the " . ucfirst($record->class) . "."
            ];

            if ($address == null) {
                $passed = "fail";
                $level_data['location'] = [
                    "passed" => "fail",
                    "qa_id" => "REQ_LOCATION_ADDRESS",
                    "message" => "At least one location address is required for the " . ucfirst($record->class) . "."
                ];
            }
        }

        return ["passed" => $passed, "data" => $level_data];

    }

    /**
     * Returns quality level 3 report
     *
     * @param $record
     * @param $xml
     * @return array
     */
    public static function checkLevelThreeQuality($record, $xml)
    {
        $passed = "passed";
        $level_data = [];

        if ($record->class == 'party') {
            $level_data['identifier'] = [
                "passed" => "passed",
                "qa_id" => "REC_IDENTIFIER",
                "message" => "At least one identifier is recommended for the " . ucfirst($record->class) . "."
            ];
            $identifier = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:identifier');

            if ($identifier == null) {
                $passed = "fail";
                $level_data['identifier'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_IDENTIFIER",
                    "message" => "At least one identifier is recommended for the " . ucfirst($record->class) . "."
                ];
            }

            $level_data['relatedObject'] = [
                "passed" => "passed",
                "qa_id" => "REC_RELATED_OBJECT_ACTIVITY",
                "message" => "It is recommended that the " . ucfirst($record->class) . " be related to at least one Activity record."
            ];

            if (RelationshipProvider::hasRelatedClass($record, 'activity') == false) {
                $passed = "fail";
                $level_data['relatedObject'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_RELATED_OBJECT_ACTIVITY",
                    "message" => "It is recommended that the " . ucfirst($record->class) . " be related to at least one Activity record."
                ];
            }
        }

        if ($record->class == 'activity') {
            $level_data['relatedObject'] = [
                "passed" => "passed",
                "qa_id" => "REC_RELATED_OBJECT_COLLECTION",
                "message" => "The " . ucfirst($record->class) . " must be related to at least one Collection record if available."
            ];
            if (RelationshipProvider::hasRelatedClass($record, 'collection') == false) {
                $passed = "fail";
                $level_data['relatedObject'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_RELATED_OBJECT_COLLECTION",
                    "message" => "The " . ucfirst($record->class) . " must be related to at least one Collection record if available."
                ];
            }
        }

        if ($record->class == 'collection') {

            $level_data['identifier'] = [
                "passed" => "passed",
                "qa_id" => "REC_IDENTIFIER",
                "message" => "At least one identifier is recommended for the " . ucfirst($record->class) . "."
            ];
            $identifier = XMLUtil::getElementsByXPathFromSXML(
                $xml,
                'ro:registryObject/ro:' . $record->class . '/ro:identifier'
            );

            if ($identifier == null) {
                $passed = "fail";
                $level_data['identifier'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_IDENTIFIER",
                    "message" => "At least one identifier is recommended for the " . ucfirst($record->class) . "."
                ];
            }

            $level_data['relatedObject'] = [
                "passed" => "passed",
                "qa_id" => "REC_RELATED_OBJECT_ACTIVITY",
                "message" => "The " . ucfirst($record->class) . " must be related to at least one Activity record where available."
            ];
            if (RelationshipProvider::hasRelatedClass($record, 'activity') == false) {
                $passed = "fail";
                $level_data['relatedObject'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_RELATED_OBJECT_ACTIVITY",
                    "message" => "The " . ucfirst($record->class) . " must be related to at least one Activity record where available."
                ];
            }

            $level_data['spatial_coverage'] = [
                "passed" => "passed",
                "qa_id" => "REC_SPATIAL_COVERAGE",
                "message" => "At least one spatial coverage for the " . ucfirst($record->class) . " is recommended."
            ];
            $spatial_coverage = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:coverage/ro:spatial');
            if ($spatial_coverage == null) {
                $passed = "fail";
                $level_data['spatial_coverage'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_SPATIAL_COVERAGE",
                    "message" => "At least one spatial coverage for the " . ucfirst($record->class) . " is recommended."
                ];
            }

            $level_data['temporal_coverage'] = [
                "passed" => "passed",
                "qa_id" => "REC_TEMPORAL_COVERAGE",
                "message" => "At least one temporal coverage entry for the " . ucfirst($record->class) . " is recommended."
            ];
            $temporal_coverage = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:coverage/ro:temporal/ro:date[@type="dateFrom" or @type="dateTo"]');

            if ($temporal_coverage == null) {
                $passed = "fail";
                $level_data['temporal_coverage'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_TEMPORAL_COVERAGE",
                    "message" => "At least one temporal coverage entry for the " . ucfirst($record->class) . " is recommended."
                ];
            }

            $level_data['citationInfo'] = [
                "passed" => "passed",
                "qa_id" => "REC_CITATION",
                "message" => "Citation data for the " . ucfirst($record->class) . " is recommended."
            ];
            $citationInfo = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:citationInfo');

            if ($citationInfo == null) {
                $passed = "fail";
                $level_data['citationInfo'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_CITATION",
                    "message" => "Citation data for the " . ucfirst($record->class) . " is recommended."
                ];
            }

            $level_data['dates'] = [
                "passed" => "passed",
                "qa_id" => "REC_DATES",
                "message" => "At least one dates element is recommended for the  " . ucfirst($record->class) . "."
            ];
            $dates = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:dates/ro:date');

            if ($dates == null) {
                $passed = "fail";
                $level_data['dates'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_DATES",
                    "message" => "At least one dates element is recommended for the  " . ucfirst($record->class) . "."
                ];
            }

        }

        if ($record->class == 'service') {
            $address = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic[string-length(.) > 0]');

            $level_data['location'] = [
                "passed" => "passed",
                "qa_id" => "REC_LOCATION_ADDRESS_ELECTRONIC",
                "message" => "At least one electronic address is required for the " . ucfirst($record->class) . " if available."
            ];

            if ($address == null) {
                $passed = "fail";
                $level_data['location'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_LOCATION_ADDRESS_ELECTRONIC",
                    "message" => "At least one electronic address is required for the " . ucfirst($record->class) . " if available."
                ];
            }

            $accessPolicy = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:accessPolicy[string-length(.) > 0]');

            $level_data['accessPolicy'] = [
                "passed" => "passed",
                "qa_id" => "REC_ACCESS_POLICY",
                "message" => "At least one Access Policy URL is recommended for the " . ucfirst($record->class) . " record."
            ];

            if ($accessPolicy == null) {
                $passed = "fail";
                $level_data['accessPolicy'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_ACCESS_POLICY",
                    "message" => "At least one Access Policy URL is recommended for the " . ucfirst($record->class) . " record."
                ];
            }

            $level_data['relatedObject'] = [
                "passed" => "passed",
                "qa_id" => "REC_RELATED_OBJECT_PARTY",
                "message" => "It is recommended that the " . ucfirst($record->class) . " be related to at least one Party record."
            ];
            if (RelationshipProvider::hasRelatedClass($record, 'party') == false) {
                $passed = "fail";
                $level_data['relatedObject'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_RELATED_OBJECT_PARTY",
                    "message" => "It is recommended that the " . ucfirst($record->class) . " be related to at least one Party record."
                ];
            }
        }

        if ($record->class == 'party' || $record->class == 'service') {
            // test description
            $brief_description = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:description[@type="brief"][string-length(.) > 0]');
            $full_description = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:description[@type="full"][string-length(.) > 0]');

            $level_data['description'] = [
                "passed" => "passed",
                "qa_id" => "REC_DESCRIPTION_FULL",
                "message" => "At least one description (brief and/or full) is recommended for the " . ucfirst($record->class) . "."
            ];
            if ($brief_description == null && $full_description == null) {
                $passed = "fail";
                $level_data['description'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_DESCRIPTION_FULL",
                    "message" => "At least one description (brief and/or full) is recommended for the " . ucfirst($record->class) . "."
                ];
            }
        }

        if ($record->class == 'party' || $record->class == 'activity') {
            $address = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address');

            $level_data['location'] = [
                "passed" => "passed",
                "qa_id" => "REC_LOCATION_ADDRESS",
                "message" => "At least one location address is recommended for the " . ucfirst($record->class) . "."
            ];

            if ($address == null) {
                $passed = "fail";
                $level_data['location'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_LOCATION_ADDRESS",
                    "message" => "At least one location address is recommended for the " . ucfirst($record->class) . "."
                ];
            }

            $level_data['existenceDates'] = [
                "passed" => "passed",
                "qa_id" => "REC_EXISTENCEDATE",
                "message" => "Existence dates are recommended for the " . ucfirst($record->class) . "."
            ];
            $existenceDates = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:existenceDates');

            if ($existenceDates == null) {
                $passed = "fail";
                $level_data['existenceDates'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_EXISTENCEDATE",
                    "message" => "Existence dates are recommended for the " . ucfirst($record->class) . "."
                ];
            }
        }

        if ($record->class == 'party' || $record->class == 'activity' || $record->class == 'collection') {
            $level_data['subject'] = [
                "passed" => "passed",
                "qa_id" => "REC_SUBJECT",
                "message" => "At least one subject (e.g. anzsrc-for code) is recommended for the " . ucfirst($record->class) . " is required."
            ];
            $subject = XMLUtil::getElementsByXPathFromSXML($xml,
                'ro:registryObject/ro:' . $record->class . '/ro:subject[string-length(.) > 0]');

            if ($subject == null) {
                $passed = "fail";
                $level_data['subject'] = [
                    "passed" => "fail",
                    "qa_id" => "REC_SUBJECT",
                    "message" => "At least one subject (e.g. anzsrc-for code) is recommended for the " . ucfirst($record->class) . " is required."
                ];
            }
        }

        return ["passed" => $passed, "data" => $level_data];
    }


    /**
     * Saving qualityInfo
     * to attributes and metadata
     *
     * @param $record
     * @param $quality_report
     * @return mixed
     */
    public static function saveQualityInfo($record, $quality_report)
    {
        foreach ($quality_report as $key => $value) {
            if (in_array($key, static::$attributeKeys)) {
                $record->setRegistryObjectAttribute($key, $value);
            } elseif (in_array($key, static::$metadataKeys)) {
                $record->setRegistryObjectMetadata($key, $value);
            }
        }
        return $quality_report;
    }

    /**
     * Format qualityInfo
     * to HTML
     *
     * @param $record
     * @param $quality_info
     * @return string
     */
    public static function formatQualityInfo($record, $quality_info)
    {
        $message = '<div id="qa_level_results" roKey="' . $record->key . '">';
        $message .= '<span class="qa_ok" level="1">Registry Objects</span>';
        $message .= '<span class="qa_ok" level="1">Registry Object</span>';
        $message .= '<span class="qa_ok" level="1">' . ucfirst($record->class) . '</span>';
        $quality_info = json_decode($quality_info);

        foreach ($quality_info as $level => $info) {
            foreach ($info->data as $key => $values) {
                $qa_id = '';
                if ($values->passed == 'passed') {
                    $class = 'class="qa_ok"';
                } else {
                    $class = 'class="qa_error"';
                }

                if (isset($values->qa_id)) {
                    $qa_id = 'qa_id="' . $values->qa_id . '" ';
                }

                $message .= '<span ' . $class . ' level="' . $level . '" ' . $qa_id . 'field_id="errors_' . $key . '">' . $values->message . '</span>';
            }
        }

        $message .= "</div>";

        return $message;
    }

}