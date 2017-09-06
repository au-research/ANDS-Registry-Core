<?php

namespace ANDS\Registry\API;

class Request
{
    public static function get($key)
    {
        return array_key_exists($key, $_GET) ? $_GET[$key] : null;
    }

    public static function value($key, $default = null)
    {
        $value = self::get($key);
        if ($value) {
            return $value;
        }
        return $default;
    }
}