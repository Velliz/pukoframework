<?php

namespace pukoframework\cache;

use Exception;
use Memcached;
use pukoframework\config\Config;
use pukoframework\peh\PukoException;
use Redis;

/**
 * Class PukoCache
 * @package pukoframework\cache
 */
class PukoCache implements CacheItemPoolInterface
{

    /**
     * @var PukoCache
     */
    public static $cacheObject;

    /**
     * @var Redis|Memcached
     */
    protected $cache;

    /**
     * @var string
     */
    protected $kind = 'MEMCACHED';

    /**
     * @var int
     */
    protected $expired = 100;

    /**
     * @return PukoCache
     * @throws Exception
     */
    public static function Get()
    {
        if (is_object(self::$cacheObject)) {
            return self::$cacheObject;
        }
        return new PukoCache();
    }

    /**
     * PukoCache constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $options = Config::Data('app')['cache'];
        $this->expired = $options['expired'];
        $this->kind = $options['kind'];
        switch ($this->kind) {
            case 'MEMCACHED':
                $this->cache = new Memcached();
                $this->cache->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
                if (!count($this->cache->getServerList())) {
                    $this->cache->addServers(array(
                        array($options['host'], $options['post']),
                    ));
                }
                break;
            case 'REDIS':
                $this->cache = new Redis();
                $this->cache->connect($options['host'], $options['post']);
                break;
            default:
                throw new Exception('cache driver not supported.');
                break;
        }
    }

    /**
     * @return Memcached|Redis
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws PukoException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem($key)
    {
        return $this->cache->get($key);
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param string[] $keys
     *   An indexed array of keys of items to retrieve.
     *
     * @throws PukoException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return array|\Traversable
     *   A traversable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = array())
    {

        if ($this->kind === 'MEMCACHED') {
            return $this->cache->getMulti($keys);
        } else {
            return $this->cache->getMultiple($keys);
        }
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *   The key for which to check existence.
     *
     * @throws PukoException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if item exists in the cache, false otherwise.
     */
    public function hasItem($key)
    {
        if ($this->kind === 'MEMCACHED') {
            $val = $this->cache->get($key);
            if (!$val) {
                return false;
            }
            return true;
        } else {
            return $this->cache->exists($key);
        }
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear()
    {
        if ($this->kind === 'MEMCACHED') {
            return $this->cache->flush();
        } else {
            return $this->cache->flushAll();
        }
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     *   The key to delete.
     *
     * @throws PukoException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem($key)
    {
        return $this->cache->delete($key);
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param string[] $keys
     *   An array of keys that should be removed from the pool.
     * @throws PukoException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys)
    {
        if ($this->kind === 'MEMCACHED') {
            return $this->cache->deleteMulti($keys);
        } else {
            foreach ($keys as $val) {
                $this->cache->delete($val);
            }
            return true;
        }
    }

    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(CacheItemInterface $item)
    {
        return $this->cache->set($item->getKey(), $item->get(), $this->expired);
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        if (!$this->cache->isPersistent()) {
            return false;
        }
        return $this->cache->set($item->getKey(), $item->get(), $this->expired);
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit()
    {
        return $this->cache->bgsave();
    }
}