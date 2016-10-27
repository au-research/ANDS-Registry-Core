<?php


namespace ANDS\Util;


use Predis\Client as PredisClient;

class NotifyUtil
{
    public static function notify($channel, $content)
    {
        $redis = new PredisClient();
//        return $redis->publish($channel, $content);
    }
}