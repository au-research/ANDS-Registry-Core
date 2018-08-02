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

    public static function getSelective(
        RegistryObject $record,
        $only = ['relationships', 'recordData']
    ) {
        $data = [];
        if (in_array('relationships', $only)) {
            $data['relationships'] = RelationshipProvider::getMergedRelationships($record, $includeDuplicate = false);
        }

        if (in_array('duplicates_relationships', $only)) {
            $data['relationships'] = RelationshipProvider::getMergedRelationships($record);
        }

        if (in_array('recordData', $only)) {
            $data['recordData'] = $record->getCurrentData()->data;
        }

        return $data;
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
                $results['temporal'][] = [
                    'dateFrom' => (string) $temporal->dateFrom,
                    'dateTo' => (string) $temporal->dateTo
                ];
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
                'rightsStatement' => [],
                'licence' => [],
                'accessRights' => []
            ];
        };
        return $results;
    }
}