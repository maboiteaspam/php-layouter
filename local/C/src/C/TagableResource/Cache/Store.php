<?php
namespace C\TagableResource\Cache;

use C\TagableResource\TagedResource;
use Moust\Silex\Cache\CacheInterface;

class Store{

    public $prefix = 'httpcache-';
    /**
     * @var CacheInterface
     */
    public $cache;

    public function __construct (CacheInterface $cache) {
        $this->cache = $cache;
    }

    public function store (TagedResource $resource, $content) {
        $etag = $resource->originalTag;
        $this->cache->store("{$this->prefix}resource-{$etag}.php",
            serialize($resource));
        $this->cache->store("{$this->prefix}content-{$etag}.php",
            serialize($content));
    }

    public function getResource ($etag) {
        $f = "{$this->prefix}resource-{$etag}.php";
        if ($this->cache->exists($f)) {
            return unserialize($this->cache->fetch($f));
        }
        return false;
    }

    public function getContent ($etag) {
        $f = "{$this->prefix}content-{$etag}.php";
        if ($this->cache->exists($f)) {
            return unserialize($this->cache->fetch($f));
        }
        return false;
    }
}