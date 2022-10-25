<?php


namespace ANDS\Util;


use Predis\Client as PredisClient;

class NotifyUtil
{
    public static function notify($channel, $content)
    {
        try {
            $redisUrl = Config::get("app.redis_url");
            $redis = new PredisClient($redisUrl);
            $redis->ping();
            return $redis->publish($channel, $content);
        } catch (\Exception $e) {
            // log notify error
            return false;
        }
    }
}