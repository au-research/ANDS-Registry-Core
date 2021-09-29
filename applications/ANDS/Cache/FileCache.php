<?php


namespace ANDS\Cache;

use Symfony\Component\Cache\Simple\FilesystemCache;

class FileCache extends AbstractCacheStorage
{
    public function __construct($path, $namespace = 'registry', $ttl = 60)
    {
        $this->setCache(new FileSystemCache($namespace, $ttl, $path));
    }
}