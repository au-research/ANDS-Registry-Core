<?php

namespace ANDS\Registry\API;

class Request
{

    public static function all()
    {
        return array_merge(self::get(), self::input());
    }

    public static function only($fields = [])
    {
        return collect(self::all())->filter(function($item, $key) use ($fields){
            return in_array($key, $fields);
        })->toArray();
    }

    public static function get($key = null)
    {
        if (!$key) {
            return $_GET;
        }
        return array_key_exists($key, $_GET) ? $_GET[$key] : null;
    }

    public static function post($key = null)
    {
        if (!$key) {
            return $_POST;
        }
        return array_key_exists($key, $_POST) ? $_POST[$key] : null;
    }

    public static function input($key = null)
    {
        $inputs = [];

        $contentType = array_key_exists('CONTENT_TYPE', $_SERVER) ? $_SERVER['CONTENT_TYPE'] : "";

        if (strpos($contentType, "x-www-form-urlencoded") !== false) {
            $result = [];
            parse_str(file_get_contents("php://input"),$result);
            $inputs = array_merge($inputs, $result);
        }

        if (strpos($contentType, "application/json") !== false) {
            $inputs = array_merge(
                $inputs,
                json_decode(file_get_contents("php://input"), true)
            );
        }

        if (strpos($contentType, "multipart/form-data") !== false) {
            throw new \Exception("multipart/form-data is not supported. Use x-www-form-urlencoded instead");
        }

        if (!$key) {
            return $inputs;
        }

        return array_key_exists($key, $inputs) ? $inputs[$key] : null;
    }

    function parse_raw_http_request(array &$a_data)
    {
        // read incoming data
        $input = file_get_contents('php://input');

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block) {
            if (empty($block))
                continue;

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== false) {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s",
                    $block, $matches);
            } // parse all other fields
            else {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s',
                    $block, $matches);
            }
            $a_data[$matches[1]] = $matches[2];
        }
    }

    public static function hasKey($key)
    {
        if (self::get($key) || self::post($key)) {
            return true;
        }

        return false;
    }

    public static function hasKeys($keys = [])
    {
        foreach ($keys as $key) {
            if (!self::hasKey($key)) {
                return false;
            }
        }
        return true;
    }

    public static function getMissing($required)
    {
        return collect($required)->filter(function($key){
            return !self::value($key);
        })->toArray();
    }



    public static function value($key, $default = null)
    {
        if ($value = self::get($key)) {
            return $value;
        }

        if ($value = self::input($key)) {
            return $value;
        }

        return $default;
    }

    /**
     * Returns the IP Address of the request
     *
     * @return string
     */
    public static function ip()
    {
        $serverVariables = ["HTTP_X_FORWARDED_FOR". "HTTP_CLIENT_IP", "REMOTE_ADDR"];

        foreach ($serverVariables as $var) {
            if (array_key_exists($var, $_SERVER)) {
                return $_SERVER[$var];
            }
        }

        // command line?
        return "127.0.0.1";
    }
}