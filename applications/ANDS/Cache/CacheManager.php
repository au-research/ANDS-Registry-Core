<?php


namespace ANDS\Cache;

use ANDS\Util\Config;
use Symfony\Component\Cache\Simple\AbstractCache;
use Symfony\Component\Cache\Simple\FilesystemCache;

class CacheManager
{
    /**
     * @param string $name
     * @return AbstractCache
     * @throws \Exception
     */
    public static function driver($name = "file")
    {
        $config = Config::get('app.cache');
        if ($name === "file") {
            $file = $config['storage']['file'];
            return new FilesystemCache(
                $file['namespace'],
                $file['ttl'],
                $file['path']
            );
        }
        // TODO: redis

        throw new \Exception("driver $name not found. ");
    }

    /**
     * @return string
     */
    public static function path()
    {
        return CACHE_PATH . '/storage';
    }
}