<?php

namespace ANDS\Registry\Providers;

use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class MetadataProvider implements RegistryContentProvider
{

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        return;
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record)
    {
        return self::getSelective($record);
    }

    /**
     * @param RegistryObject $record
     * @param $only  String, no existing use for relationship here so just get the recordData
     * @return array
     */
    public static function getSelective(RegistryObject $record, $only = ['recordData']) {
        $aData = [];
        foreach($only as $content){
            if(strtolower($content) == 'recorddata'){
                $aData[$content] = $record->getCurrentData()->data;
            }else{
                $aData[$content] = $content . " Information is no longer provided by this sevice!";
            }
        }
        return $aData;
    }

    /**
     * @param $record
     * @return string
     * @throws \Exception
     */
    public static function getOriginatingSource($record)
    {
        $xml = XMLUtil::getSimpleXMLFromString($record->getCurrentData()->data);
        return (string) $xml->registryObject->originatingSource;
    }

    /**
     * @param RegistryObject $record
     * @param null $xml
     * @return array
     */
    public static function getDescriptions(RegistryObject $record, $xml = null)
    {
        $xml = $xml ? $xml : $record->getCurrentData()->data;

        $descriptions = [];
        $xpath = "ro:registryObject/ro:{$record->class}/ro:description";

        foreach (XMLUtil::getElementsByXPath($xml, $xpath) AS $description) {
            $descriptions[] = [
                'type' => (string) $description["type"],
                'value' => (string) $description
            ];
        };

        return $descriptions;
    }

    public static function getSpatial(RegistryObject $record, $xml = null)
    {
        $xml = $xml ? $xml : $record->getCurrentData()->data;
        $xpath = "ro:registryObject/ro:{$record->class}/ro:coverage/ro:spatial";

        $results = [];
        foreach (XMLUtil::getElementsByXPath($xml, $xpath) AS $spatial) {
            $results[] = [
                'type' => (string) $spatial['type'],
                'value' => (string) $spatial
            ];
        }
        return $results;
    }

    public static function getElectronicAddress(RegistryObject $record, $xml = null)
    {
        $xml = $xml ? $xml : $record->getCurrentData()->data;

        $results = [];
        foreach (XMLUtil::getElementsByXPath($xml, '//ro:address/ro:electronic') as $electronic) {
            $results[] = [
                'type' => (string) $electronic['type'],
                'value' => (string) $electronic->value
            ];
        }
        return $results;
    }

    public static function getPhysicalAddress(RegistryObject $record, $xml = null)
    {
        $xml = $xml ? $xml : $record->getCurrentData()->data;

        $results = [];
        foreach (XMLUtil::getElementsByXPath($xml, '//ro:address/ro:physical') as $address) {
            $results[] = [
                'type' => (string) $address['type'],
                'value' => (string) $address->addressPart
            ];
        }
        return $results;
    }

    public static function getCoverages(RegistryObject $record, $xml = null)
    {
        $xml = $xml ? $xml : $record->getCurrentData()->data;
        $xpath = "ro:registryObject/ro:{$record->class}/ro:coverage";

        $results = [
            'spatial' => [],
            'temporal' => []
        ];

        foreach (XMLUtil::getElementsByXPath($xml, $xpath) AS $element) {
            foreach ($element->spatial as $spatial) {
                $results['spatial'][] = [
                    'type' => (string) $spatial['type'],
                    'value' => (string) $spatial
                ];
            }

            foreach ($element->temporal as $temporal) {
                $dates = [];
                foreach ($temporal->date as $date) {
                    $dates[] = [
                        'type' => (string) $date['type'],
                        'format' => (string) $date['format'],
                        'value' => (string) $date
                    ];
                }
                $results['temporal'][] = $dates;
            }

            $results[] = [
                'rightsStatement' => [],
                'licence' => [],
                'accessRights' => []
            ];
        };
        return $results;
    }

    /**
     * TODO fill out rights information
     * @param RegistryObject $record
     * @param null $xml
     * @return array
     */
    public static function getRights(RegistryObject $record, $xml = null)
    {
        $xml = $xml ? $xml : $record->getCurrentData()->data;
        $xpath = "ro:registryObject/ro:{$record->class}/ro:rights";

        $results = [];
        foreach (XMLUtil::getElementsByXPath($xml, $xpath) AS $element) {
            $results[] = [
                'rightsStatement' => [
                    'uri' => (string) $element->rightsStatement['rightsUri'],
                    'value' => (string) $element->rightsStatement
                ],
                'licence' => [
                    'uri' => (string) $element->licence['rightsUri'],
                    'value' => (string) $element->licence
                ],
                'accessRights' => [
                    'uri' => (string) $element->accessRights['rightsUri'],
                    'value' => (string) $element->accessRights
                ]
            ];
        };
        return $results;
    }

    /**
     * @param RegistryObject $record
     * @param null $simpleXML
     * @return array
     * @throws \Exception
     */
    public static function getRelatedInfoTypes(RegistryObject $record, $simpleXML = null)
    {
        $simpleXML = $simpleXML ? $simpleXML : MetadataProvider::getSimpleXML($record);

        $relatedInfoTypes = [];
        foreach ($simpleXML->xpath("//ro:relatedInfo/@type") as $type) {
            $relatedInfoTypes[] = (string) $type;
        }
        return $relatedInfoTypes;
    }

    /**
     * Get publisher for the record
     * Spec for DCI
     * @param RegistryObject $record
     * @param null $simpleXML
     * @return string
     * @throws \Exception
     */
    public static function getPublisher(RegistryObject $record, $simpleXML = null)
    {
        /**
         * registryObject:collection:citationInfo:citationMetadata:publisher
            OR registryObject@Group
         */
        $simpleXML = $simpleXML ? $simpleXML : MetadataProvider::getSimpleXML($record);

        // registryObject:collection:citationInfo:citationMetadata:publisher
        $publisher = $simpleXML->xpath('//ro:citationInfo/ro:citationMetadata/ro:publisher');
        if (count($publisher) > 0 && $elem = array_pop($publisher)) {
            return (string) $elem;
        }

        return $record->group;
    }

    /**
     * @param RegistryObject $record
     * @param null $simpleXML
     * @return null|string
     * @throws \Exception
     */
    public static function getVersion(RegistryObject $record, $simpleXML = null)
    {
        $simpleXML = $simpleXML ? $simpleXML : MetadataProvider::getSimpleXML($record);

        // registryObject:collection:citationInfo:citationMetadata:version
        $version = $simpleXML->xpath('//ro:citationInfo/ro:citationMetadata/ro:version');
        if (count($version) > 0 && $elem = array_pop($version)) {
            return (string) $elem;
        }

        return null;
    }

    /**
     * Get the Source URL for a record
     * Spec for DCI
     *
     * @param RegistryObject $record
     * @param null $simpleXML
     * @return array|null
     * @throws \Exception
     */
    public static function getSourceURL(RegistryObject $record, $simpleXML = null)
    {
        /**
         *registryObject:collection:citationInfo:citationMetadata:identifier: type="doi"
        OR registryObject:collection:citationInfo:citationMetadata:identifier: type="handle"
        OR registryObject:collection:citationInfo:citationMetadata:identifier: type="uri"
        OR registryObject:collection:citationInfo:citationMetadata:identifier: type="purl"
        OR registryObject:collection:identifier:type="doi"
        OR registryObject:collection:identifier:type="handle"
        OR registryObject:collection:identifier:type="uri"
        OR registryObject:collection:identifier:type="purl"
        OR registryObject:collection:citationInfo:citationMetadata:identifier: type="url"
        OR registryObject:collection:location:address:electronic:type="url"
         */
        $simpleXML = $simpleXML ? $simpleXML : MetadataProvider::getSimpleXML($record);

        $xpaths = [
            "//ro:citationMetadata/ro:identifier[@type='doi']",
            "//ro:citationMetadata/ro:identifier[@type='handle']",
            "//ro:citationMetadata/ro:identifier[@type='uri']",
            "//ro:citationMetadata/ro:identifier[@type='purl']",
            "//ro:collection/ro:identifier[@type='doi']",
            "//ro:collection/ro:identifier[@type='handle']",
            "//ro:collection/ro:identifier[@type='uri']",
            "//ro:collection/ro:identifier[@type='purl']",
            "//ro:citationMetadata/ro:identifier[@type='url']",
            "//ro:location/ro:address/ro:electronic[@type='url']",
        ];

        foreach ($xpaths as $xpath) {
            $source = $simpleXML->xpath($xpath);
            if (count($source) && $elem = array_pop($source)) {
                $value = $elem->value ? (string)$elem->value : (string)$elem;
                $value = trim($value);
                if ($value == "") {
                    continue;
                }
                return [
                    'type' => (string)$elem['type'],
                    'value' => $value
                ];
            }
        }

        return null;
    }

    /**
     * Useful helper function to get simplexml for a record
     * Use for fallback when simplexml is not provided
     *
     * @param RegistryObject $record
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public static function getSimpleXML(RegistryObject $record)
    {
        return XMLUtil::getSimpleXMLFromString($record->getCurrentData()->data);
    }
}