<?php


namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class TemporalProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TO DO - Create process function
    }

    /**
     * Return the available temporal dates
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function get(RegistryObject $record)
    {
        return DatesProvider::getDateCoverages($record);
    }

    /**
     * Return the indexable values for date_from, date_to,
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getIndexableArray(RegistryObject $record)
    {
        return [
            'date_from' => self::getDateFrom($record),
            'date_to' => self::getDateTo($record),
            'earliest_year' => self::getEarliestYear($record),
            'latest_year' =>self::getLatestYear($record),
        ];
    }

    public static function getDateFrom(RegistryObject $record){

        $date_from = [];
        $dates = self::get($record);
        foreach($dates as $date){
            if($date['type'] == 'dateFrom') {
                $date_val = self::getWTCdate($date['value']);
                if($date_val !== false){
                    $date_from[] = $date_val;
                }
            }
        }
        return $date_from;
    }

    public static function getDateTo(RegistryObject $record){

        $date_to= [];
        $dates = self::get($record);
        foreach($dates as $date){
            if($date['type'] == 'dateTo') {
                $date_val = self::getWTCdate($date['value']);
                if($date_val !== false){
                    $date_to[] = $date_val;
                }
            }
        }
        return $date_to;
    }

    public static function getEarliestYear(RegistryObject $record){

        $earliest_year = '';
        $dates = self::get($record);
        foreach ($dates AS $date) {
            if ($date['type'] == 'dateFrom') {
                if (strlen(trim($date['value'])) == 4)
                    $date['value'] = "Jan 1, " . $date['value'];
                $start = strtotime($date['value']);
                $earliest_year = date("Y", $start);
            }
        }
        return $earliest_year;
    }

    public static function getLatestYear(RegistryObject $record){

        $latest_year = '';
        $dates = self::get($record);
        foreach ($dates AS $date) {
            if ($date['type'] == 'dateTo') {
                if (strlen(trim($date['value'])) == 4)
                    $date['value'] = "Dec 31, " . $date['value'];
                $end = strtotime($date['value']);
                $latest_year = date("Y", $end);
            }
        }
        return $latest_year;
    }



    public static function getWTCdate($value)
    {
        utc_timezone();
        // "Year and only year" (i.e. 1960) will be treated as HH SS by default
        if (strlen($value) == 4) {
            // Assume this is a year:
            $value = "Jan 1 " . $value;
        } else if (strlen($value) == 7 && preg_match("/\d{4}\-\d{2}/", $value) === 1) {
            // add day only if it's yyyy-mm
            //RDA-770
            $value = $value . "-01";
        }

        if (($timestamp = strtotime($value)) === false) {
            return false;
        } else {
            return date('Y-m-d\TH:i:s\Z', $timestamp);
        }
        reset_timezone();
    }

}