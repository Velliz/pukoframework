<?php

namespace pukoframework\cache;

use Memcached;
use Redis;

/**
 * Class PukoCache
 * @package pukoframework\cache
 */
class PukoCache
{

    const MEMCACHED = 1;
    const REDIS = 2;

    /**
     * @param null $type
     * @param $driver
     * @throws CacheException
     *
     * @return CacheItemPoolInterface
     */
    public static function make($type = null, $driver = null)
    {
        if (null === $type) {
            return static::autoDiscovery();
        }
        switch ($type) {
            case self::REDIS:
                if (!static::isRedisAvailable() || !$driver instanceof Redis) {
                    throw new PukoCacheException('Redis cache not available: not installed or argument not a Redis instance');
                }
                return new RedisDriver($driver);
            case self::MEMCACHED:
                if (!static::isMemcachedAvailable() || !$driver instanceof Memcached) {
                    throw new PukoCacheException('Redis cache not available: not installed or argument not a Redis instance');
                }
                return new MemcachedDriver($driver);
            default:
                throw new PukoCacheException('invalid user cache pool type');
        }
    }

    /**
     * @return CacheItemPoolInterface
     * @throws CacheException
     */
    private static function autoDiscovery()
    {
        // is redis installed and available in localhost standard port?
        if (static::isRedisAvailable()) {
            $redis = new Redis();
            if ($redis->connect('127.0.0.1', 6379)) {
                return new RedisDriver($redis);
            };
        }
        throw new PukoCacheException('could not find a suitable cache pool');
    }

    /**
     * @return bool
     */
    private static function isRedisAvailable()
    {
        return class_exists('Redis');
    }

    /**
     * @return bool
     */
    private static function isMemcachedAvailable()
    {
        return (class_exists('Memcached'));
    }
}