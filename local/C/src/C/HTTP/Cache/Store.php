<?php
namespace C\HTTP\Cache;

use C\TagableResource\TagedResource;
use Moust\Silex\Cache\CacheInterface;

class Store{

    public $prefix = 'prefix-';
    /**
     * @var CacheInterface
     */
    public $cache;
    public $storeName;

    public function __construct ($storeName, CacheInterface $cache) {
        $this->cache = $cache;
        $this->storeName = $storeName;
    }

    public function store (TagedResource $resource, $url, $content) {
        $surl = sha1($url);
        $etag = $resource->originalTag;
        $this->cache->store("{$this->storeName}resource-{$etag}.php", $resource);
        $this->cache->store("{$this->storeName}content-{$etag}.php", $content);
        $this->cache->store("{$this->storeName}url-{$surl}.php", $etag);
    }

    public function getEtag ($url) {
        $surl = sha1($url);
        $f = "{$this->storeName}url-{$surl}.php";
        return $this->cache->fetch($f);
    }

    public function getResource ($etag) {
        $f = "{$this->storeName}resource-{$etag}.php";
        return $this->cache->fetch($f);
    }

    public function getContent ($etag) {
        $f = "{$this->storeName}content-{$etag}.php";
        return ($this->cache->fetch($f));
    }
}