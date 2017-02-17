<?php


namespace ANDS\Registry\Providers;


use ANDS\Registry\Providers\RIFCS\DatesProvider;
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
        $data = MetadataProvider::get($record);

        $doc = new ScholixDocument;

        $doc->set('link', [
            'publicationDate' => DatesProvider::getPublicationDate($record, $data),
            'publisher' => [
                'name' => $record->group,
                'identifiers' => self::getIdentifiers($record, $data['recordData'])
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

    public static function getIdentifiers(RegistryObject $record, $xml = null)
    {
        $identifiers = [];

        if (!$xml) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
            $xml = $data['recordData'];
        }

        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            $identifiers[] = [
                'identifier' => (string) $identifier,
                'schema' => (string) $identifier['type']
            ];
        }

        return $identifiers;
    }
}