<?php

namespace ANDS\Cache;


use Symfony\Component\Filesystem\Filesystem;

class FileCacheTest extends \RegistryTestClass
{
    protected $testPath = "/tmp/auto_fileCache_test_registry";

    /** @test */
    public function test_construct()
    {
        $cache = new FileCache($this->testPath);

        $this->assertInstanceOf(FileCache::class, $cache);
    }

    /** @test */
    function it_gets_cache_driver()
    {
        $cache = Cache::driver('file');

        $this->assertInstanceOf(FileCache::class, $cache);
    }

    /** @test */
    function it_gets_cache_driver_by_static()
    {
        $cache = Cache::file();

        $this->assertInstanceOf(FileCache::class, $cache);
    }

    /** @test */
    function it_stores_and_get_correctly()
    {
        $cache = new FileCache($this->testPath);

        $this->assertTrue($cache->put("key", ['fish']));
        $this->assertTrue($cache->has("key"));
        $this->assertEquals(['fish'], $cache->get("key"));
    }

    /** @test */
    function it_forgets_correctly()
    {
        $cache = new FileCache($this->testPath);

        $this->assertTrue($cache->put("key", ['fish']));
        $this->assertTrue($cache->has("key"));

        $this->assertTrue($cache->forget("key"));
        $this->assertFalse($cache->has("key"));
        $this->assertEquals(null, $cache->get("key"));
    }

    /** @test */
    function it_expires_correctly()
    {
        $cache = new FileCache($this->testPath);

        $this->assertTrue($cache->put("key", ['fish'], 0.01));
        sleep(1);
        $this->assertFalse($cache->has("key"));
    }

    /** @test */
    function it_remembers_correctly()
    {
        $cache = new FileCache($this->testPath);
        $cache->remember("key", 0.01, function() {
            return ['fish'];
        });

        sleep(1);
        $this->assertFalse($cache->has("key"));
    }

    public function tearDown()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove([$this->testPath]);

        return parent::tearDown();
    }
}
