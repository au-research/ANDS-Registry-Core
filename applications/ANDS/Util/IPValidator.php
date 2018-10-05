<?php


namespace ANDS\Util;


class IPValidator
{
    /**
     * Test a string free form IP against a range
     * @param  string $ip       the ip address to test on
     * @param  string $ip_range the ip address to match on / or a comma separated list of ips to match on
     * @return bool             returns true if the ip is found in some manner that match the ip range
     */
    public static function validate($ip, $ip_range)
    {
        // first lets get the lists of ip address or ranges separated by commas
        $ip_ranges = explode(',', $ip_range);
        foreach ($ip_ranges as $ip_range) {
            $ip_range = explode('-', $ip_range);
            if (sizeof($ip_range) > 1) {
                $target_ip = ip2long($ip);
                // If exactly 2, then treat the values as the upper and lower bounds of a range for checking
                // AND the target_ip is valid
                if (count($ip_range) == 2 && $target_ip) {
                    // convert dotted quad notation to long for numeric comparison
                    $lower_bound = ip2long($ip_range[0]);
                    $upper_bound = ip2long($ip_range[1]);
                    // If the target_ip is valid
                    if ($target_ip >= $lower_bound && $target_ip <= $upper_bound) {
                        return true;
                    }

                }

            } else {
                if (self::ip_match($ip, $ip_range[0])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * a helper function for test_ip
     * @param  string $ip       the ip address of most any form to test
     * @param  string $match    the ip to perform a matching truth test
     * @return bool             return true/false depends on if the first ip match the second ip
     */
    public static function ip_match($ip, $match)
    {
        if (ip2long($match)) {//is an actual IP
            if ($ip == $match) {
                return true;
            }
        } else {//is something weird
            if (strpos($match, '/')) {//if it's a cidr notation
                if (self::cidr_match($ip, $match)) {
                    return true;
                }
            } else {//is a random string (let's say a host name)
                $match = gethostbyname($match);
                if ($ip == $match) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * match an ip against a cidr
     * @param  string $ip the ip address to perform a truth test on
     * @param  string $range the cidr in the form of xxx.xxx.xxx.xxx/xx to match on
     * @return bool       return true/false depends on the truth test
     */
    public static function cidr_match($ip, $range)
    {
        list ($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask; // nb: in case the supplied subnet wasn't correctly aligned
        return ($ip & $mask) == $subnet;
    }
}