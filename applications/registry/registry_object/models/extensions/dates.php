<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dates_Extension extends ExtensionBase
{

    private $minYear = 9999999;
    private $maxYear = 0;

    private $date_mappings = array(
        "dc.created" => "Created",
        "dc.published" => "Published",
        "dc.available" => "Available",
        "dc.dateAccepted" => "Accepted",
        "dc.dateSubmitted" => "Submitted",
        "dc.issued" => "Issued",
        "dc.valid" => "Valid",
    );

    function __construct($ro_pointer)
    {
        parent::__construct($ro_pointer);
    }

    function processTemporal()
    {

        $this->minYear = 9999999;
        $this->maxYear = 0;
        $temporalArray = array();
        $sxml = $this->ro->getSimpleXML();
        //TODO: fix me...
        $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
        $temporals = $sxml->xpath('//ro:temporal/ro:date');
        foreach ($temporals AS $temporal) {
            $type = (string)$temporal["type"];
            $value = $this->getWTCdate((string)$temporal);
            if ($value)
                $temporalArray[] = array('type' => $type, 'value' => $value);
        }
        return $temporalArray;
    }

    function getEarliestAsYear()
    {
        //TODO: write the function :-)
        return $this->minYear;
    }

    function getLatestAsYear()
    {
        //TODO: write the function :-)
        return $this->maxYear;
    }

    function getWTCdate($value)
    {
        utc_timezone();
        // "Year and only year" (i.e. 1960) will be treated as HH SS by default
        if (strlen($value) == 4) {
            // Assume this is a year:
            $value = "Jan 1 " . $value;
        } else if (preg_match("/\d{4}\-\d{2}/", $value) === 1) {
            $value = $value . "-01";
        }

        if (($timestamp = strtotime($value)) === false) {
            return false;
        } else {
            $date = getDate($timestamp);
            if ($date['year'] > $this->maxYear)
                $this->maxYear = $date['year'];
            if ($date['year'] < $this->minYear)
                $this->minYear = $date['year'];
            return date('Y-m-d\TH:i:s\Z', $timestamp);
        }

        reset_timezone();
    }

    function formatDates($sxml_dates, $capitalise = false)
    {
        $friendly_date = '';

        $to = array();
        $from = array();
        $other = array();

        if ($sxml_dates) {
            foreach ($sxml_dates AS $date_entry) {
                $formatted_date = $this->getWTCdate((string)$date_entry);
                if ($formatted_date) {
                    if (strpos(strtolower((string)$date_entry['type']), "to") !== FALSE) {
                        $to[] = $this->nicifyDate($formatted_date);
                    } elseif (strpos(strtolower((string)$date_entry['type']), "from") !== FALSE) {
                        $from[] = $this->nicifyDate($formatted_date);
                    } else {
                        $other[] = $this->nicifyDate($formatted_date);
                    }
                }
            }
        }

        $from_text = ($capitalise ? "From " : "");
        if ($to && $from) {
            $friendly_date .= $from_text . implode($from, ", ") . " to " . implode($to, ", ");
        } elseif ($from) {
            $friendly_date .= $from_text . implode($from, ", ");
        } else {
            $friendly_date .= implode(array_merge($to, $from, $other), ", ");
        }

        return $friendly_date;
    }


    function nicifyDate($w3cdtf)
    {
        utc_timezone();

        $time = strtotime($w3cdtf);
        if (!$time) {
            //we need to cater for the instance when someone legitimately enters 1st jan 1970
            if ($w3cdtf == "1970-01-01T00:00:00Z") {
                return "1970";
            } else {
                return false;
            }
        }

        if (date("H:i:s", $time) == "00:00:00") {
            if (date("m-d", $time) == "01-01") {
                // Assume friendly display of just the year
                return date("Y", $time); // i.e. 2001
            } else {
                // Assume friendly display of full date (and no time)
                return date("Y-m-d", $time);    // i.e.  March 10, 2001
            }
        } else {
            // Assume friendly display of full date and time
            return date("Y-m-d H:i", $time);    // i.e.  March 10, 2001, 5:16 pm
        }
        reset_timezone();
    }

    /**
     * Returns the earliest year in existenceDates
     * @param bool|false $xml
     * @return bool|string
     */
    function getExistenceDateEarliestYear($xml = false)
    {
        $earliestYear = false;
        if (!$xml) $xml = $this->ro->getSimpleXML();
        foreach ($xml->xpath('//ro:existenceDates') AS $date) {
            if ($date->startDate) {
                if (strlen(trim($date->startDate)) == 4)
                    $date->startDate = "Jan 1, " . $date->startDate;
                $start = strtotime($date->startDate);
                $earliestYear = date("Y", $start);
            }
        }
        return $earliestYear;
    }

    /**
     * Returns the latest year in existenceDates
     * @param bool|false $xml
     * @return bool|string
     */
    function getExistenceDateLatestYear($xml = false)
    {
        $latestYear = false;
        if (!$xml) $xml = $this->ro->getSimpleXML();
        foreach ($xml->xpath('//ro:existenceDates') AS $date) {
            if ($date->endDate) {
                if (strlen(trim($date->endDate)) == 4)
                    $date->endDate = "Dec 31, " . $date->endDate;
                $end = strtotime($date->endDate);
                $latestYear = date("Y", $end);
            }
        }
        return $latestYear;
    }

    /**
     * Return the existenceDate
     * @param $point
     * @param string $format
     * @param $xml
     * @return bool|string
     */
    function getExistenceDate($point, $format='Y-m-d', $xml)
    {
        if (!$xml) $xml = $this->ro->getSimpleXML();
        foreach ($xml->xpath('//ro:existenceDates') AS $date) {
            if ($date->{$point}) {
                if (strlen(trim($date->{$point})) == 4) {
                    $date->{$point} = "Dec 31, " . $date->{$point};
                }
                $date = date(strtotime($date->{$point}));
                return date($format, strtotime($date));
            }
        }
    }

}
