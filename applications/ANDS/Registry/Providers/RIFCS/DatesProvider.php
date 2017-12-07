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

        $publicationDate = null;

        $citationMedataDates = XMLUtil::getElementsByXPath(
            $data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:date'
        );

        // registryObject/collection/citationInfo/citationMetadata/date[@type=’publication date’]
        foreach ($citationMedataDates AS $date) {
            if ((string) $date['type'] == 'publicationDate') {
                $publicationDate = self::formatDate((string) $date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        // registryObject/collection/citationInfo/citationMetadata/date[@type=’issued date’]
        foreach ($citationMedataDates AS $date) {
            if ((string) $date['type'] == 'issued') {
                $publicationDate = self::formatDate((string) $date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        // registryObject/collection/citationInfo/citationMetadata/date[@type=’created’]
        foreach ($citationMedataDates AS $date) {
            if ((string) $date['type'] == 'created') {
                $publicationDate = self::formatDate((string) $date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;


        // first citationMetadata/date found
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:date') AS $date) {
            $value = (string) $date;
            $publicationDate = self::formatDate($value, $format);
        }
        if ($publicationDate) return $publicationDate;

        $roDates = XMLUtil::getElementsByXPath(
            $data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:dates'
        );

        // registryObject/collection/dates[@type=’issued’]
        foreach ($roDates AS $date) {
            if ((string) $date['type'] == 'dc.issued') {
                $publicationDate = self::formatDate((string) $date->date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        // registryObject/collection/dates[@type=’available’]
        foreach ($roDates AS $date) {
            if ((string) $date['type'] == 'dc.available') {
                $publicationDate = self::formatDate((string) $date->date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        // registryObject/collection/dates[@type=’created’]
        foreach ($roDates AS $date) {
            if ((string) $date['type'] == 'dc.created') {
                $publicationDate = self::formatDate((string) $date->date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        /**
         * registryObject/collection@dateModified
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class) AS $object) {

            if ($dateModified = (string) $object['dateModified']) {
                $publicationDate = self::formatDate($dateModified, $format);
            }
        }
        if ($publicationDate) return $publicationDate;


        /**
         * registryObject/Collection@dateAccessioned
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class) AS $object) {

            if ($dateAccessioned = (string) $object['dateAccessioned']) {
                $publicationDate = self::formatDate($dateAccessioned, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

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

        if (self::validateDate($value, 'm-Y')) {
            return $value;
        }

        if (self::validateDate($value, 'Y-m')) {
            return $value;
        }

        if (self::isValidTimeStamp($value)) {
            return Carbon::createFromTimestamp($value)->format($format);
        }

        // last try
        try {
            return (new Carbon($value))->format($format);
        } catch (\Exception $e) {
            // TODO: log the date type we can't parse
            return null;
        }
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
        try {
            $d = Carbon::createFromFormat($format, $date);
            return $d && $d->format($format) === $date;
        } catch (\Exception $e) {
            // try again
        }
        $d = self::parseDate($date);
        return $d && $d->format($format) === $date;
    }

    public static function parseDate($date)
    {
        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            // not a parsable date
        }

        $formats = [
            'Y-m-d',
            'Y',
            'Y-m',
            'm-Y',
            'd-m-Y'
        ];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $date);
                return $parsed;
            } catch (\Exception $e) {
                // not a parsable date
            }
        }

        return null;
    }


}