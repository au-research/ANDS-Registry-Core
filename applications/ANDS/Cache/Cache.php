<?php


namespace ANDS\Cache;


use ANDS\Util\Config;
use Closure;

class Cache
{
    /**
     * @param $key
     * @return mixed|null
     */
    public static function get($key)
    {
        try {
            return static::cache()->get($key);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $minutes
     * @return bool
     */
    public static function put($key, $value, $minutes)
    {
        return static::cache()->set($key, $value, $minutes * 60);
    }

    /**
     * @param $key
     * @return bool
     */
    public static function has($key)
    {
        return static::cache()->has($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public static function forget($key)
    {
        return static::cache()->delete($key);
    }

    /**
     * @param $key
     * @param $minutes
     * @param $callback
     * @return mixed|null
     */
    public static function remember($key, $minutes, Closure $callback)
    {
        if (!static::isEnabled()) {
            return $callback();
        }

        $value = static::cache()->get($key);

        if (! is_null($value)) {
            return $value;
        }

        $value = $callback();

        static::cache()->set($key, $value, $minutes);

        return $value;
    }

    public static function rememberForever($key, Closure $callback)
    {
        return static::remember($key, null, $callback);
    }

    /**
     * @return bool
     */
    public static function flush()
    {
        return static::cache()->clear();
    }

    public static function isEnabled()
    {
        $config = Config::get('app.cache');

        return $config['enabled'];
    }

    /**
     * @return \Symfony\Component\Cache\Simple\AbstractCache
     */
    public static function cache()
    {
        $config = Config::get('app.cache');

        return CacheManager::driver($config['default']);
    }
}