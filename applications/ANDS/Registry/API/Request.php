<?php

namespace ANDS\Registry\API;

class Request
{
    public static function get($key)
    {
        return array_key_exists($key, $_GET) ? $_GET[$key] : null;
    }

    public static function post($key)
    {
        return array_key_exists($key, $_POST) ? $_POST[$key] : null;
    }

    public static function value($key, $default = null)
    {
        if ($value = self::get($key)) {
            return $value;
        }

        if ($value = self::post($key)) {
            return $value;
        }

        return $default;
    }
}