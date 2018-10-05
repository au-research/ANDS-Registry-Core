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

        if ($type === "file") {
            $file = $config['storage']['file'];
            return new FileCache($file['path'], $file['namespace'], $file['ttl']);
        }

        return null;
    }
}