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
     * @var string
     */
    public $sfExt;
    /**
     * @var CacheInterface
     */
    public $cache;

    public function __construct (CacheInterface $cache, $ext, $sfExt) {
        $this->cache = $cache;
        $this->ext = $ext;
        $this->sfExt = $sfExt;
    }

    public function isExt ($ext) {
        return $ext===$this->ext;
    }

    public function sfFmt () {
        return $this->sfExt;
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