<?php


namespace ANDS\Cache;


use ANDS\Util\Config;
use Closure;

class Cache
{
    /**
     * @return AbstractCacheStorage
     */
    public static function file()
    {
        return static::driver('file');
    }

    /**
     * @param $type
     * @return AbstractCacheStorage
     */
    public static function driver($type)
    {
        $config = Config::get('app.cache');

        if (!in_array($type, array_keys($config['store']))) {
            return null;
        }

        $store = $config['store'][$type];

        $driver = $store['driver'];
        if ($driver === "file") {
            $driverConfig = array_merge($config['drivers']['file'], $store);
            return new FileCache($driverConfig['path'], $driverConfig['namespace'], $driverConfig['ttl']);
        }

        // implement other type of driver, ie. redis

        return null;
    }
}