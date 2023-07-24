<?php


namespace ANDS\Cache;


use ANDS\Util\Config;
use Closure;
use Psr\SimpleCache\InvalidArgumentException;

abstract class AbstractCacheStorage implements CacheInterface
{
    /** @var \Symfony\Component\Cache\Simple\AbstractCache */
    private $cache;

    public function get($key)
    {
        try {
            return $this->cache->get($key);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function has($key)
    {
        return $this->cache->has($key);
    }

    public function put($key, $value, $minutes = null)
    {

        $ttl = $minutes ? intval(ceil($minutes * 60)) : null;

        try {
            return $this->cache->set($key, $value, $ttl);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function forget($key)
    {
        return $this->cache->delete($key);
    }

    public function flush()
    {
        return $this->cache->clear();
    }

    public function remember($key, $minutes, Closure $callback)
    {
        if (! $this->enabled()) {
            return $callback();
        }

        $value = $this->get($key);

        if (! is_null($value)) {
            return $value;
        }

        $value = $callback();

        $this->put($key, $value, $minutes);

        return $value;
    }

    public function enabled()
    {
        $config = Config::get('app.cache.enabled');
        return $config['enabled'];
    }

    public function rememberForever($key, Closure $callback)
    {
        return $this->remember($key, 525600, $callback);
    }

    /**
     * @param \Symfony\Component\Cache\Simple\AbstractCache $cache
     * @return AbstractCacheStorage
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }
}