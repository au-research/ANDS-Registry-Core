<?php


namespace ANDS\Util;


use Predis\Client as PredisClient;

class NotifyUtil
{
    public static function notify($channel, $content)
    {
        try {
            $redis = new PredisClient();
            $redis->ping();
            return $redis->publish($channel, $content);
        } catch (\Exception $e) {
            // log notify error
            return false;
        }
    }
}