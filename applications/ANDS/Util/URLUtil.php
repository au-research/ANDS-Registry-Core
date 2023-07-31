<?php


namespace ANDS\Util;


use Exception;

class URLUtil
{

    /**
     * @param string $input A base64 encoded string
     *
     * @return string A decoded string
     */
    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * @param string $input Anything really
     *
     * @return string The base64 encode of what you passed in
     */
    public static function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * @param string $url the url of the file
     *
     * @return string The content of the file
     * CC-2839
     * Disable ssl verify peer due to OpenSSL running into error:14090086 while establishing a connection
     * with a https web server (in this case it should be local/trusted) with ISRG Root X1
     * todo consider using guzzle or curl for http requests instead of file_get_contents
     * @see https://www.php.net/manual/en/migration56.openssl.php
     * @see https://letsencrypt.org/docs/dst-root-ca-x3-expiration-september-2021/
     */
    public static function file_get_contents($url) {
        $httpContext = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];
        $content = "";
        try{
            $content = file_get_contents($url, false, stream_context_create($httpContext));
        }catch(Exception $e){
            $content = "";
        }
        return $content;
    }
}