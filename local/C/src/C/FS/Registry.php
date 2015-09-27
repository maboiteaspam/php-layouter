<?php

namespace C\FS;

use C\Misc\Utils;
use Moust\Silex\Cache\CacheInterface;

class Registry {

    public $storeName;
    public $cache;
    public $signature;

    public $config = [
        'basePath' => '/', // must always be an absolute path
        'paths' => [],
        'alias' => [],
    ];
    public $items = [
        'relative/file/path'=>[
            'type'          =>'file',
            'name'          =>'abc.php',
            'dir'           =>'some/relative/path/to/file',
            'path'          =>'/absolute/base/path',
            'sha1'          =>'123abc',
            'file_mtime'    =>123,
        ],
        'relative/dir/path'=>[
            'type'          =>'dir',
            'name'          =>'abc',
            'dir'           =>'some/relative/path/to/dir',
            'path'          =>'/absolute/base/path',
            'sha1'          =>'',
            'file_mtime'    =>123,
        ]
    ];

    public function __construct ($storeName, CacheInterface $cache, $config=[]) {
        $this->config = array_merge($this->config, $config);
        $this->items = [];
        $this->storeName = $storeName;
        $this->cache = $cache;
        foreach($this->config['paths'] as $i => $path) {
            $this->config['paths'][$i] = reliablePath($path);
        }
    }

    public function registerPath($path, $as=null){
        $p = realpath($path);
        if ($p===false) Utils::stderr("This path does not exists $path");
        else {
            $this->config['paths'][] = $p;
            if($as) $this->config['alias']["$as:"] = $p;
        }
        return $p;
    }
    public function setBasePath($path){
        $this->config['basePath'] = $path;
        return $path;
    }
    public function getBasePath(){
        return $this->config['basePath'];
    }

    public function isFresh(){
        return $this->signature && $this->signature===$this->sign();
    }

    public function createSignature(){
        $this->signature = $this->sign();
        return $this;
    }

    public function sign($some=null){
        $signature = '';
        $this->each(function ($item, $localPath) use(&$signature, $some) {
            if (!$some || in_array($localPath, $some) || in_array($item['absolute_path'], $some) ) {
                $signature = sha1($signature.$item['sha1']);
            }
        });
        return $signature;
    }

    public function saveToCache(){
        $dump = $this->build()->dump();
        $this->cache->store("{$this->storeName}dump", ($dump));
        return $dump;
    }
    public function clearCached(){
        $this->cache->delete('dump');
    }

    public function loadFromCache(){
        $dump = $this->cache->fetch("{$this->storeName}dump");
        if ($dump) return $this->loadFromDump(($dump));
        return false;
    }
    public function loadFromDump($dump){
        $this->config = $dump['config'];
        $this->items = $dump['items'];
        $this->signature = $dump['signature'];
        return true;
    }
    public function build(){
        return $this->recursiveReadPath()->createSignature();
    }
    public function dump(){
        return [
            'items'=>$this->items,
            'config'=>[
                'basePath' => $this->config['basePath'],
                'paths' => $this->getUniversalPath($this->config['paths']),
                'alias' => $this->getUniversalPath($this->config['alias']),
            ],
            'signature'=>$this->signature,
        ];
    }
    protected function getUniversalPath (array $paths) {
        $basePath = $this->config['basePath'];
        $ret = [];
        foreach( $paths as $index=>$path) {
            $rp = LocalFs::realpath($path);
            if ($rp===false) {
                $rp = LocalFs::realpath("$basePath".DIRECTORY_SEPARATOR."$path");
            }
            if ($rp!==false) {
                $ret[$index] = substr($rp, strlen($basePath)+1);
            } else {
                // log that something is wrong in some assets path.
            }
        }
        $ret = array_unique($ret);
        if (count($ret)!==count($paths)) {
            // mh, something weird like duplicated path.
        }
        return $ret;
    }
    protected function recursiveReadPath () {
        $paths = $this->getUniversalPath($this->config['paths']);
        $this->items = [];
        foreach( $paths as $path) {
            $Directory = new \RecursiveDirectoryIterator($path);
            $filter = new \RecursiveCallbackFilterIterator($Directory, function ($current, $key, $iterator) {
                if (in_array($current->getFilename(), ['..'])) {
                    return false;
                }
                return $current;
            });
            $Iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);

            foreach( $Iterator as $Iterated ) {
                /* @var $Iterated \SplFileInfo */
                $this->addItem($Iterated);
            }
        }
        return $this;
    }

    public function addClassFile ($className, $onlyIfNew=true) {
        $reflector = new \ReflectionClass($className);
        $path = $reflector->getFileName();
        if ($onlyIfNew && !$this->get($path) || !$onlyIfNew) {
            $this->registerPath(dirname($path));
            $this->addItem($path);
        }
    }
    public function addItem ($path) {
        $basePath = $this->config['basePath'];

        if (is_string($path)) {
            $path = new \SplFileInfo($path);
        }
        /* @var $path \SplFileInfo */
        $fp = substr($path->getRealPath(), strlen($basePath)+1);
        $p = dirname($fp)."".DIRECTORY_SEPARATOR;
        $item = [
            'type'          => $path->isFile()?'file':'dir',
            'name'          => $path->getFilename()==='.'?basename($fp):$path->getFilename(),
            'dir'           => $p,
            'sha1'          => $path->isFile()?sha1($path->getRealPath().LocalFs::file_get_contents($path->getRealPath())):'',
            'extension'     => $path->getExtension(),
            'file_mtime'    => $path->getMTime(),
            'file_atime'    => $path->getATime(),
            'file_ctime'    => $path->getCTime(),
        ];

        $key = $fp.($path->isFile()?'':DIRECTORY_SEPARATOR);
        $this->items[$key] = $item;
    }
    public function removeItem ($path) {
        $basePath = $this->config['basePath'];
        if (is_string($path)) {
            $path = new \SplFileInfo($path);
        }
        $fp = substr($path->getRealPath(), strlen($basePath)+1);
        $key = $fp.($path->isFile()?'':DIRECTORY_SEPARATOR);
        unset($this->items[$key]);
    }
    public function refreshItem ($path) {
        $this->addItem($path);
    }
    public function get($itemPath){
        $itemPath = reliablePath($itemPath);
        $basePath = $this->config['basePath'];

        $aliasPos = strpos($itemPath, ":");
        $alias = substr($itemPath, 0, $aliasPos+1);

        if ($aliasPos>2 && array_key_exists($alias, $this->config['alias'])) {
            $itemPath = str_replace($alias, $this->config['alias'][$alias], $itemPath);
        }

        if (isset($this->items[$itemPath])) {
            $item = $this->items[$itemPath];
            $item['absolute_path'] = "$basePath".DIRECTORY_SEPARATOR.$item['dir'].$item['name'];
            return $item;
        }

        if (isset($this->items["$itemPath".DIRECTORY_SEPARATOR])) {
            $item = $this->items["$itemPath".DIRECTORY_SEPARATOR];
            $item['absolute_path'] = "$basePath/".$item['dir'].$item['name'];
            return $item;
        }

        if (substr($itemPath, 0, strlen($basePath))===$basePath) {
            $p = substr($itemPath, strlen($basePath)+1);
            if (isset($this->items[$p])) {
                $item = $this->items[$p];
                $item['absolute_path'] = "$basePath".DIRECTORY_SEPARATOR.$item['dir'].$item['name'];
                return $item;
            }
            if (isset($this->items["$p".DIRECTORY_SEPARATOR])) {
                $item = $this->items["$p".DIRECTORY_SEPARATOR];
                $item['absolute_path'] = "$basePath/".$item['dir'].$item['name'];
                return $item;
            }
        }
        foreach( $this->config['paths'] as $i=>$path) {
            $p = substr($itemPath, 0, strlen($path));
            if (in_array($p, $this->config['paths'])) {
                $itemP = substr($itemPath, strlen($basePath)+1);
                if (isset($this->items[$itemP])) {
                    $item = $this->items[$itemP];
                    $item['absolute_path'] = "$basePath".DIRECTORY_SEPARATOR.$item['dir'].$item['name'];
                    return $item;
                }
            }
        }
        foreach( $this->config['paths'] as $i=>$path) {
            $p = rp("$basePath".DIRECTORY_SEPARATOR."$itemPath");
            $itemP = substr($p, strlen($basePath)+1);
            if (isset($this->items[$itemP])) {
                $item = $this->items[$itemP];
                $item['absolute_path'] = "$basePath".DIRECTORY_SEPARATOR.$item['dir'].$item['name'];
                return $item;
            }
        }
        return false;
    }
    public function each ($callback) {
        $basePath = $this->config['basePath'];
        foreach($this->items as $i=>$item) {
            $item['absolute_path'] = "$basePath".DIRECTORY_SEPARATOR.$item['dir'].$item['name'];
            $callback($item, $i);
        }
    }



}

function rp($path) {
    $out=array();
    foreach(explode(DIRECTORY_SEPARATOR, $path) as $i=>$fold){
        if ($fold=='' || $fold=='.') continue;
        if ($fold=='..' && $i>0 && end($out)!='..') array_pop($out);
        else $out[]= $fold;
    } return ($path{0}==DIRECTORY_SEPARATOR?DIRECTORY_SEPARATOR:'').join(DIRECTORY_SEPARATOR, $out);
}

function reliablePath($path) {
    $path = str_replace("/", DIRECTORY_SEPARATOR, $path);
    $path = str_replace("\\", DIRECTORY_SEPARATOR, $path);
    return $path;
}
