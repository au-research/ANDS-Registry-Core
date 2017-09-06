<?php


namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use Carbon\Carbon;
use DateTime;

class DatesProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        return;
    }

    /**
     * Return the available dates
     *
     * @param RegistryObject $record
     * @return array
     */
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
            if (in_array($type, ['publicationDate', 'issued_date', 'created'])) {
                return self::formatDate($value, $format);
            }
        }

        // first citationMetadata/date found
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:date') AS $date) {
            $value = (string) $date;
            return self::formatDate($value, $format);
        }

        /**
         * registryObject/collection/dates[@type=’issued’]
         * registryObject/collection/dates[@type=’available’]
         * registryObject/collection/dates[@type=’created’]
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:dates') AS $date) {
            $value = (string) $date;
            $type = (string) $date['type'];
            if (in_array($type, ['dc.issued', 'dc.available', 'dc.created'])) {
                return self::formatDate($value, $format);
            }
        }

        /**
         * registryObject/collection@dateModified
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class) AS $object) {

            if ($dateModified = (string) $object['dateModified']) {
                return self::formatDate($dateModified, $format);
            }
        }

        /**
         * registryObject/Collection@dateAccessioned
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class) AS $object) {

            if ($dateAccessioned = (string) $object['dateAccessioned']) {
                return self::formatDate($dateAccessioned, $format);
            }
        }

        return self::getCreatedDate($record, $format);
    }

    /**
     * Returns created date
     *
     * @param RegistryObject $record
     * @param string $format
     * @return string
     */
    public static function getCreatedDate(RegistryObject $record, $format = 'Y-m-d')
    {
        return self::formatDate(
            $record->getRegistryObjectAttributeValue('created'),
            $format
        );
    }

    /**
     * Return the date value in the given format
     * Format is Y or Y-m or Y-m-d
     * @param $value
     * @param $format
     * @return string
     */
    public static function formatDate($value, $format = 'Y-m-d')
    {
        // if it comes in as the year, just return the year
        if (self::validateDate($value, 'Y')) {
            return $value;
        }

        if (self::validateDate($value, 'Y-m')) {
            return $value;
        }

        if (self::isValidTimeStamp($value)) {
            return Carbon::createFromTimestamp($value)->format($format);
        }
        return (new Carbon($value))->format($format);
    }

    /**
     * Returns if the value is a timestamp
     *
     * @param $timestamp
     * @return bool
     */
    public static function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    /**
     * Validate if the date is of a format
     *
     * @param $date
     * @param string $format
     * @return bool
     */
    public static function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }


}