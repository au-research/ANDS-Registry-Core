<?php


namespace ANDS\Cache;

use Closure;

interface CacheInterface
{
    public function get($key);
    public function has($key);
    public function put($key, $value, $minutes);
    public function forget($key);
    public function flush();
    public function remember($key, $minutes, Closure $callback);
    public function rememberForever($key, Closure $callback);
}