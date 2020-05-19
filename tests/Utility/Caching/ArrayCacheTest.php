<?php
/** */
namespace AnrDaemon\Tests\Utility\Caching;

use AnrDaemon\Utility\Caching\ArrayCache;
use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;

/** Simple caching tests
*/
final class ArrayCacheTest
extends TestCase
{
    private $dataset = [
        "item" => "data",
    ];

    /** Test creation of an object instance
    */
    public function testCreateArrayCacheInstance()
    {
        $this->assertInstanceOf(ArrayCache::class, new ArrayCache());
    }

    /** Test created object implements PSR-16 CacheInterface
    */
    public function testInstanceImplementsCacheInterface()
    {
        $this->assertInstanceOf(CacheInterface::class, new ArrayCache());
    }

    /** Test initialize cache at creation

        @depends testInstanceImplementsCacheInterface
    */
    public function testInitializeCacheAtCreation()
    {
        $value = reset($this->dataset);
        $index = key($this->dataset);
        $cache = new ArrayCache([$index => $value]);
        $this->assertTrue($cache->has("item"));
    }

    /** Test add item to cache

        @depends testInstanceImplementsCacheInterface
    */
    public function testAddItemToCache()
    {
        $value = reset($this->dataset);
        $index = key($this->dataset);
        $cache = new ArrayCache();
        $this->assertTrue($cache->set($index, $value), "Failed adding item '$index' to cache!");
        $this->assertTrue($cache->has($index), "Item '$index' not found in cache!");
    }

    /** Test add item to cache with TTL

        @medium
        @depends testAddItemToCache
    */
    public function testAddItemToCacheWithTtl()
    {
        $value = reset($this->dataset);
        $index = key($this->dataset);
        $ttl = 2;
        $cache = new ArrayCache();
        $this->assertTrue($cache->set($index, $value, $ttl), "Failed adding item '$index' to cache!");
        $this->assertTrue($cache->has($index), "Item '$index' not found in cache!");
        sleep($ttl + 1);
        $this->assertFalse($cache->has($index), "Item '$index' is still in cache!");
    }


    /** Test add item to cache with negative TTL

        @depends testAddItemToCache
    */
    public function testAddItemToCacheWithNegativeTtl()
    {
        $value = reset($this->dataset);
        $index = key($this->dataset);
        $ttl = -1;
        $cache = new ArrayCache();
        $this->assertTrue($cache->set($index, $value, $ttl), "Failed adding item '$index' to cache!");
        $this->assertFalse($cache->has($index), "Item '$index' is in cache!");
    }

    /** Test getting item from cache

        @depends testAddItemToCache
    */
    public function testGetItemFromCache()
    {
        $value = reset($this->dataset);
        $index = key($this->dataset);
        $cache = new ArrayCache();
        $cache->set($index, $value);
        $this->assertSame($value, $cache->get($index));
    }

    /** Test deleting item from cache

        @depends testAddItemToCache
    */
    public function testDeleteItemFromCache()
    {
        $value = reset($this->dataset);
        $index = key($this->dataset);
        $cache = new ArrayCache();
        $cache->set($index, $value);
        $this->assertTrue($cache->has($index), "Item '$index' not found in cache!");
        $this->assertTrue($cache->delete($index), "Failed to delete item '$index' from cache!");
        $this->assertFalse($cache->has($index), "Item '$index' is still in cache!");
    }

    /** Test cache clearing

        @depends testAddItemToCache
    */
    public function testClearCache()
    {
        $value = reset($this->dataset);
        $index = key($this->dataset);
        $cache = new ArrayCache();
        $cache->set($index, $value);
        $this->assertTrue($cache->has($index), "Item '$index' not found in cache!");
        $this->assertTrue($cache->clear(), "Failed to clear cache!");
        $this->assertFalse($cache->has($index), "Item '$index' is still in cache!");
    }
}
