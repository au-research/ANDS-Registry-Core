<?php


namespace ANDS\Registry\Providers\RIFCS;

use ANDS\RecordData;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\Config;
use ANDS\Util\XMLUtil;
use Carbon\Carbon;
use DateTime;

class DatesProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        $record->modified_at = static::getModifiedAt($record);
        $record->created_at = static::getCreatedAt($record);
        $record->save();
        return;
    }

    public static function getDateCoverages(RegistryObject $record, $xml = null)
    {
        $xml = $xml ? $xml : $record->getCurrentData()->data;
        $xpath = "ro:registryObject/ro:{$record->class}/ro:coverage/ro:temporal/ro:date";

        $dates = [];
        foreach (XMLUtil::getElementsByXPath($xml, $xpath) AS $date) {
            $dates[] = [
                'type' => (string) $date['type'],
                'dateFormat' => (string) $date['dateFormat'],
                'value' => (string) $date
            ];
        }
        return $dates;
    }

    public static function humanReadableCoverages($dates)
    {
        if (!count($dates)) {
            return "";
        }

        $from = null;
        $to = null;

        $from = collect($dates)->filter(function($date){
           return $date['type'] === 'dateFrom';
        })->first();

        $to = collect($dates)->filter(function($date){
            return $date['type'] === 'dateTo';
        })->first();

        $other = collect($dates)->first();

        // from date to date
        // date (only from or to)
        // date (no from, no to)

        if ($from && $to) {
            return 'From '. self::formatDate($from['value']) . " to " . self::formatDate($to['value']);
        } elseif ($from && !$to) {
            return 'From '. self::formatDate($from['value']);
        } elseif(!$from && $to) {
            return self::formatDate($to['value']);
        } else {
            return self::formatDate($other);
        }

        return $from . $to;
        dd($from);

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
        // if it comes in as the year or year-month, just leave it
        if (self::validateDate($value, 'Y') || self::validateDate($value, 'Y-m')) {
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

    /**
     * @param RegistryObject $record
     * @return Carbon
     */
    public static function getModifiedAt(RegistryObject $record)
    {
        // latest record data timestamp
        $data = RecordData::where('registry_object_id', $record->id)->orderBy('timestamp', 'desc')->first();
        return Carbon::createFromTimestamp($data->timestamp);
    }

    /**
     * @param RegistryObject $record
     * @return Carbon
     */
    public static function getCreatedAt(RegistryObject $record)
    {
        //earliest record data timestamp
        $data = RecordData::where('registry_object_id', $record->id)->orderBy('timestamp', 'asc')->first();
        return Carbon::createFromTimestamp($data->timestamp);
    }

    public static function touchSync(RegistryObject $record)
    {
        $record->synced_at = Carbon::now();
        $record->save();
    }

    public static function touchDelete(RegistryObject $record)
    {
        $record->deleted_at = Carbon::now();
        $record->save();
    }

    public static function getUpdatedAt($record, $getDateFormat = 'Y-m-d', $timezone = null)
    {
        $timezone = $timezone ?: Config::get('app.timezone');
        return Carbon::parse($record->modified_at)->setTimezone($timezone)->format($getDateFormat);
    }

    /**
     * @param $time
     * @return Carbon
     */
    public static function parseUTCToLocal($time)
    {
        return Carbon::parse($time, 'UTC')->setTimezone(Config::get('app.timezone'));
    }


}