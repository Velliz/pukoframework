<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.0
 */

namespace pukoframework\pdc;

use Memcached;
use pte\PteCache;
use pukoframework\cache\MemcachedDriver;
use pukoframework\cache\PukoCache;
use pukoframework\config\Config;
use pukoframework\Response;

/**
 * Class Template
 * @package pukoframework\pdc
 */
class Template implements Pdc, PteCache
{

    var $key;
    var $value;
    var $switch;

    /**
     * @var MemcachedDriver
     */
    var $cache;

    /**
     * @param $clause
     * @param $command
     * @param $value
     */
    public function SetCommand($clause, $command, $value = null)
    {
        $this->key = $clause;
        $this->value = $command;
        $this->switch = $value;
    }

    /**
     * @param Response &$response
     * @return mixed
     * @throws \Exception
     * @throws \pukoframework\cache\CacheException
     */
    public function SetStrategy(Response &$response)
    {
        switch ($this->value) {
            case 'master':
                if (strcasecmp(str_replace(' ', '', $this->switch), 'false') === 0) {
                    $response->useMasterLayout = false;
                }
                break;
            case 'html':
                if (strcasecmp(str_replace(' ', '', $this->switch), 'false') === 0) {
                    $response->useHtmlLayout = false;
                }
                break;
            case 'cache':
                if (strcasecmp(str_replace(' ', '', $this->switch), 'true') === 0) {

                    $cacheConfig = Config::Data('app')['cache'];

                    $memcached = new Memcached($cacheConfig['identifier']);
                    $memcached->addServer($cacheConfig['host'], $cacheConfig['port']);
                    $this->cache = PukoCache::make(PukoCache::MEMCACHED, $memcached);

                    $response->cacheDriver = $this;
                }
                break;
        }

        return true;
    }

    /**
     * @param $templateKeys
     * @return array|false
     */
    public function GetTemplate($templateKeys)
    {
        $templateKeys = hash('ripemd160', $templateKeys);
        $item = $this->cache->getItem($templateKeys);
        if ($item->isHit()) {
            return $item->get();
        }
        return false;
    }

    /**
     * @param $templateKeys
     * @param $templateData
     * @return array
     */
    public function SetTemplate($templateKeys, $templateData)
    {
        $templateKeys = hash('ripemd160', $templateKeys);
        $item = $this->cache->getItem($templateKeys)->set($templateData);
        return $item->get();
    }
}