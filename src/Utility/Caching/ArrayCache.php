<?php
/** PSR-16 compatible cache class based on regular array
*/
namespace AnrDaemon\Utility\Caching;

use AnrDaemon\Exceptions\InvalidCollectionException;
use AnrDaemon\Exceptions\InvalidKeyException;
use Psr\SimpleCache\CacheInterface;

/** Simple caching class wrapped around an array
*/
class ArrayCache
implements CacheInterface
{
    /** @var array Internal cache storage

        Storage format is ["key" => ["expires" => <time>, "data" => <stored content>]].
    */
    protected $cache = [];

    /** Validates if $key is a scalar value

        @param scalar $key A cache key being validated.

        @return void
    */
    protected function validateKey($key) {
        if(!is_scalar($key)) {
            throw new InvalidKeyException("The '$key' is not a valid key!");
        }
    }

    /** Validates if cache entry is (still?) valid

        Checks if a cache entry exists and not yet expired.

        @param scalar $key A cache key being validated.

        @return bool true if cached key is present and not yet expred.
    */
    protected function isValid($key) {
        if(!isset($this->cache[$key])) {
            return false;
        }

        if(isset($this->cache[$key]["expires"]) && $this->cache[$key]["expires"] < microtime(true)) {
            unset($this->cache[$key]);
            return false;
        }

        return true;
    }

    /** Fetches a value from the cache by its unique key

        @param string $key The unique key of the item in the cache.
        @param mixed $default Default value to return if the key does not exist.

        @return mixed The value of the item from the cache, or $default in case of cache miss.

        @throws \Psr\SimpleCache\InvalidArgumentException if $key is not a legal value for array key.
    */
    public function get($key, $default = null) {
        $this->validateKey($key);
        return $this->isValid($key) ? $this->cache[$key]["data"] : $default;
    }

    /** Persists data in the cache, uniquely referenced by a key with an optional TTL value

        @param string $key The key of the item to store.
        @param mixed $value The value of the item to store. Must be serializable.
        @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
        the driver supports TTL, then the library may set a default value for it or let the driver take care of that.

        @return bool True on success or false on failure.

        @throws \Psr\SimpleCache\InvalidArgumentException if $key is not a legal value for array key.
    */
    public function set($key, $value, $ttl = null) {
        $this->validateKey($key);
        $this->cache[$key] = [
            "data" => $value,
            "expires" => isset($ttl) ? microtime(true) + $ttl : null,
        ];

        return true;
    }

    /** Deletes an item from the cache by its unique key

        @param string $key The unique cache key of the item to delete.

        @return bool True if the item was successfully removed. False if there was an error.

        @throws \Psr\SimpleCache\InvalidArgumentException if $key is not a legal value for array key.
    */
    public function delete($key) {
        $this->validateKey($key);
        unset($this->cache[$key]);

        return true;
    }

    /** Wipes clean the entire cache

        @return bool True on success and false on failure.
    */
    public function clear() {
        $this->cache = [];

        return true;
    }

    /** Obtains multiple cache items by their unique keys

        @param iterable $keys A list of keys that can be obtained in a single operation.
        @param mixed $default Default value to return for keys that do not exist.

        @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.

        @throws \Psr\SimpleCache\InvalidArgumentException if $keys is neither an array nor a Traversable,
        or if any of the $keys are not a legal value.
    */
    public function getMultiple($keys, $default = null) {
        if(!is_iterable($keys)) {
            throw new InvalidCollectionException("Collection is not iterable");
        }

        $result = [];
        foreach($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /** Persists a set of [key => value] pairs in the cache, with an optional TTL

        @param iterable $values A list of key => value pairs for a multiple-set operation.
        @param null|int|\DateInterval $ttl Optional. The TTL value for the items. If no value is set and the
        driver supports TTL, then the library may set a default value for it or let the driver take care of that.

        @return bool True on success and false on failure.

        @throws \Psr\SimpleCache\InvalidArgumentException if $keys is neither an array nor a Traversable,
        or if any of the $keys are not a legal value.
    */
    public function setMultiple($values, $ttl = null) {
        if(!is_iterable($values)) {
            throw new InvalidCollectionException("Collection is not iterable");
        }

        foreach($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /** Deletes multiple cache items in a single operation

        @param iterable $keys A list of keys to be deleted.

        @return bool True on success and false on failure.

        @throws \Psr\SimpleCache\InvalidArgumentException if $keys is neither an array nor a Traversable,
        or if any of the $keys are not a legal value.
    */
    public function deleteMultiple($keys) {
        if(!is_iterable($keys)) {
            throw new InvalidCollectionException("Collection is not iterable");
        }

        foreach($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /** Determines whether an item is present in the cache

        NOTE: It is recommended that has() is only to be used for cache warming type purposes
        and not to be used within your live applications operations for get/set, as this method
        is subject to a race condition where your has() will return true and immediately after,
        another script can remove it, making the state of your app out of date.

        @param string $key The unique key of the item in the cache.

        @return bool True if key exists and not yet expired.

        @throws \Psr\SimpleCache\InvalidArgumentException if $key is not a legal value for array key.
    */
    public function has($key) {
        $this->validateKey($key);
        return $this->isValid($key);
    }

    /** Constructs an instance with optionally prefilled datasource

        @see \Psr\SimpleCache\CacheInterface::setMultiple()

        @param iterable $source Cache source to get data from.
        @param numeric $ttl Default TTL for initial records.

        @return void

        @throws \Psr\SimpleCache\InvalidArgumentException if any of the $source keys is not a legal value for array key.
    */
    public function __construct(iterable $source = null, $ttl = null) {
        if($source) {
            $this->setMultiple($source, $ttl);
        }
    }
}
