<?php

if (!function_exists('env')) {
    function env($env, $default = "")
    {
        return getenv($env) ?: $default;
    }
}

if (!function_exists('baseUrl')) {
    function baseUrl($suffix = "")
    {
        return env('PROTOCOL') . env('BASE_URL') . '/'. $suffix;
    }
}

if (!function_exists('request')) {
    function request($key, $default = null) {
        return ANDS\Registry\API\Request::value($key, $default);
    }
}

/**
 * Create URL Title
 *
 * Takes a "title" string as input and creates a
 * human-friendly URL string with a "separator" string
 * as the word separator.
 *
 * @access	public
 * @param	string	the string
 * @param	string	the separator
 * @return	string
 */
if ( ! function_exists('url_title'))
{
    function url_title($str, $separator = '-', $lowercase = FALSE)
    {
        if ($separator == 'dash')
        {
            $separator = '-';
        }
        else if ($separator == 'underscore')
        {
            $separator = '_';
        }

        $q_separator = preg_quote($separator);

        $trans = array(
            '&.+?;'                 => '',
            '[^a-z0-9 _-]'          => '',
            '\s+'                   => $separator,
            '('.$q_separator.')+'   => $separator
        );

        $str = strip_tags($str);

        foreach ($trans as $key => $val)
        {
            $str = preg_replace("#".$key."#i", $val, $str);
        }

        if ($lowercase === TRUE)
        {
            $str = strtolower($str);
        }

        return trim($str, $separator);
    }
}

if (!function_exists('retry')) {
    function retry($f, $delay = 10, $retries = 3)
    {
        try {
            return $f();
        } catch (Exception $e) {
            if ($retries > 0) {
                sleep($delay);
                return retry($f, $delay, $retries - 1);
            } else {
                throw $e;
            }
        }
    }
}
