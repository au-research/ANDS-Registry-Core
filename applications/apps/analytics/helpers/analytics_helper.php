<?php

/**
 * Read a local file path and return the lines
 * @author  Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @param  string $file File Path Local
 * @return array()
 */
function readFileToLine($file)
{
    $lines = array();
    if (file_exists($file)) {
        $file_handle = fopen($file, "r");
        while (!feof($file_handle)) {
            $line = fgets($file_handle);
            $lines[] = $line;
        }
        fclose($file_handle);
    }
    return $lines;
}

/**
 * Read a string in the form of [key:value]
 * and return an array of key=>value in PHP
 * @author  Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @param  string $string
 * @return array(key=>value)
 */
function readString($string)
{
    $result = array();
    preg_match_all("/\[([^\]]*)\]/", $string, $matches);
    foreach ($matches[1] as $match) {
        $array = explode(':', $match, 2);
        if ($array && is_array($array) && isset($array[0]) && isset($array[1])) {
            $result[$array[0]] = $array[1];
        }
    }
    return $result;
}

/**
 * Creating date collection between two dates
 *
 * <code>
 * <?php
 * # Example 1
 * date_range("2014-01-01", "2014-01-20", "+1 day", "m/d/Y");
 *
 * # Example 2. you can use even time
 * date_range("01:00:00", "23:00:00", "+1 hour", "H:i:s");
 * </code>
 *
 * @author Ali OYGUR <alioygur@gmail.com>
 * @param string since any date, time or datetime format
 * @param string until any date, time or datetime format
 * @param string step
 * @param string date of output format
 * @return array
 */
function date_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y')
{

    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while ($current <= $last) {
        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

/**
 * Read a directory and return a list of files
 * exclude the . and .. directory
 * @param  string $directory Directory Path Local
 * @return array
 */
function readDirectory($directory)
{
    $scanned_directory = array_diff(scandir($directory), array('..', '.'));
    return $scanned_directory;
}

function getIPLocation($ip) {
    $data = @file_get_contents('http://ip-api.com/json/'.$ip);
    if ($data) {
        $data = json_decode($data, true);
        return $data;
    } else {
        return false;
    }
}