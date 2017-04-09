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