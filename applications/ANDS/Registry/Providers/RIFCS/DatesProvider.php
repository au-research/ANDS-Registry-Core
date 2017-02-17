<?php


namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use Carbon\Carbon;

class DatesProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        return;
    }

    public static function get(RegistryObject $record)
    {

        $data = MetadataProvider::get($record);

        return [
            'publicationDate' => self::getPublicationDate($record, $data, 'Y-m-d')
        ];
    }

    /**
     * @param RegistryObject $record
     * @param null $data
     * @param string $format
     * @return string
     */
    public static function getPublicationDate(
        RegistryObject $record,
        $data = null,
        $format = 'Y-m-d'
    ) {
        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
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
                return (new Carbon($value))->format($format);
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
                return (new Carbon($value))->format($format);
            }
        }

        /**
         * registryObject/Collection@dateModified
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class) AS $object) {

            if ($dateAccessioned = (string) $object['dateAccessioned']) {
                return (new Carbon($dateAccessioned))->format($format);
            }

            if ($dateModified = (string) $object['dateModified']) {
                return (new Carbon($dateModified))->format($format);
            }
        }

        // date the record was ingested into the Registry as last resort
        $value = $record->getRegistryObjectAttributeValue('created');

        return (new Carbon($value))->format($format);
    }
}