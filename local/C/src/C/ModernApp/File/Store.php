<?php
namespace C\ModernApp\File;

use C\FS\KnownFs;
use C\FS\LocalFs;
use Moust\Silex\Cache\CacheInterface;
use Symfony\Component\Yaml\Yaml;

class Store {

    /**
     * @var KnownFs
     */
    protected $modernFS;

    /**
     * @var CacheInterface
     */
    protected $cache;

    public function setModernLayoutFS (KnownFs $fs) {
        $this->modernFS = $fs;
    }

    public function setCache(CacheInterface $cache) {
        $this->cache = $cache;
    }

    public function buildFile ($filePath) {
        $layoutFile     = $this->getFileMeta($filePath);
        $layoutStruct   = Yaml::parse (LocalFs::file_get_contents ($layoutFile['absolute_path']), true, false, true);
        $this->cache->store($layoutFile['dir'].'/'.$layoutFile['name'], $layoutStruct);
        return $layoutStruct;
    }

    public function getFileMeta ($filePath) {
        $layoutFile = $this->modernFS->get($filePath);
        if( $layoutFile===false) {
            throw new \Exception("File not found $filePath");
        }
        return $layoutFile;
    }

    public function get ($filePath) {
        $layoutFile     = $this->getFileMeta($filePath);
        $layoutStruct   = $this->cache->fetch($layoutFile['dir'].'/'.$layoutFile['name']);
        if (!$layoutStruct) {
            $layoutStruct = $this->buildFile($filePath);
        }
        return $layoutStruct;
    }
}