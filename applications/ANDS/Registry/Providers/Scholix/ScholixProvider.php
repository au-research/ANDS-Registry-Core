<?php


namespace ANDS\Registry\Providers;


use ANDS\Registry\Providers\Scholix\ScholixDocument;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use Carbon\Carbon;

class ScholixProvider implements RegistryContentProvider
{
    protected static $scholixableAttr = "scholixable";

    /**
     * if the record is a collection
     * and is related to a type of publication
     *
     * @param RegistryObject $record
     * @param null $relationships
     * @return bool
     */
    public static function isScholixable(RegistryObject $record, $relationships = null)
    {
        // early return if it's not a collection
        if ($record->class != "collection") {
            return false;
        }

        // search through combined relationships to see if there's a related publication
        if (!$relationships) {
            $relationships = RelationshipProvider::getMergedRelationships($record);
        }

        $types = collect($relationships)->map(function($item) {
            return $item->prop('to_related_info_type') ?: $item->prop('to_type');
        })->toArray();

        if (!in_array('publication', $types)) {
            return false;
        }

        return true;
    }

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        $record->deleteRegistryObjectAttribute(self::$scholixableAttr);

        $relationships = RelationshipProvider::getMergedRelationships($record);
        if (self::isScholixable($record, $relationships)) {
            $record->setRegistryObjectAttribute(self::$scholixableAttr, true);
        }

        // TODO implement get and store
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return ScholixDocument
     */
    public static function get(RegistryObject $record)
    {
        $data = self::getRecordData($record);

        $doc = new ScholixDocument;

        $doc->set('link', [
            'publicationDate' => self::getPublicationDate($record, $data),
            'publisher' => [
                'name' => $record->group,
                'identifiers' => self::getIdentifiers($record, $data)
            ],
            'linkProvider' => [
                'name' => 'Australian National Data Service',
                'identifiers' => [
                    'identifier' =>  'http://nla.gov.au/nla.party-1508909',
                    'schema' => 'AU-ANL:PEAU'
                ]
            ]
        ]);

        return $doc;
    }

    /**
     * Returns the publicationDate of a scholix document
     * Business Rules implemented within
     *
     * @param RegistryObject $record
     * @param null $data
     * @return string
     */
    public static function getPublicationDate(RegistryObject $record, $data = null)
    {
        if (!$data) {
            $data = self::getRecordData($record);
        }

        /*
         * registryObject/collection/citationInfo/citationMetadata/date[@type=’publication date’]
         * registryObject/collection/citationInfo/citationMetadata/date[@type=’issued date’]
         * registryObject/collection/citationInfo/citationMetadata/date[@type=’created’]
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:date') AS $date) {
            $value = (string) $date;
            $type = (string) $date['type'];
            if (in_array($type, ['publication_date', 'issued_date', 'created'])) {
                return (new Carbon($value))->format('Y-m-d');
            }
        }

        /**
         * registryObject/collection/dates[@type=’issued’]
         * registryObject/collection/dates[@type=’available’]
         * registryObject/collection/dates[@type=’created’]
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:date') AS $date) {
            $value = (string) $date;
            $type = (string) $date['type'];
            if (in_array($type, ['issued', 'available', 'created'])) {
                return (new Carbon($value))->format('Y-m-d');
            }
        }

        /**
         * registryObject/Collection@dateModified
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class) AS $object) {

            if ($dateAccessioned = (string) $object['dateAccessioned']) {
                return (new Carbon($dateAccessioned))->format('Y-m-d');
            }

            if ($dateModified = (string) $object['dateModified']) {
                return (new Carbon($dateModified))->format('Y-m-d');
            }
        }

        $value = $record->getRegistryObjectAttributeValue('created');
        return (new Carbon($value))->format('Y-m-d');
    }

    public static function getIdentifiers(RegistryObject $record, $data = null)
    {
        $identifiers = [];

        if (!$data) {
            $data = self::getRecordData($record);
        }

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            $identifiers[] = [
                'identifier' => (string) $identifier,
                'schema' => (string) $identifier['type']
            ];
        }

        return $identifiers;
    }

    public static function getRecordData(RegistryObject $record)
    {
        $relationships = RelationshipProvider::getMergedRelationships($record);
        $recordData = $record->getCurrentData()->data;

        return compact('relationships', 'recordData');
    }
}