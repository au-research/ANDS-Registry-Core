<?php

if (!function_exists('env')) {
    function env($env, $default = "")
    {
        return getenv($env) ?: $default;
    }
}