<?php

namespace C\Intl;

use Moust\Silex\Cache\CacheInterface;
use Silex\Application;

abstract class AbstractIntlLoader implements IntlLoaderInterface {

    /**
     * @var string
     */
    public $ext;
    /**
     * @var CacheInterface
     */
    public $cache;

    public function __construct (CacheInterface $cache, $ext) {
        $this->cache = $cache;
        $this->ext = $ext;
    }

    public function isExt ($ext) {
        return $ext===$this->ext;
    }

    public function clearCache () {
        return $this->cache->clear();
    }

    public function loadFromCache ($file) {
        return $this->cache->fetch($file);
    }

    public function removeFile ($file) {
        return $this->cache->delete($file);
    }

    public function saveToCache ($file) {
        $dump = $this->load($file);
        $this->cache->store($file, $dump);
        return $dump;
    }

    public abstract function load ($file);
}