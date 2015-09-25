<?php

namespace C\FS;

use C\Misc\Utils;

class Registry {

    public $file;
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

    public function __construct ($file, $config=[]) {
        $this->config = array_merge($this->config, $config);
        $this->items = [];
        $this->file = $file;
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
            if (!$some || in_array($localPath, $some) || in_array($item['absolute_path'], $some) )
                $signature = sha1($item['sha1']);
        });
        return $signature;
    }

    public function saveToFile(){
        $dump = $this->build();
        if (!LocalFs::is_dir(dirname($this->file))) LocalFs::mkdir(dirname($this->file), 0700, true);
        LocalFs::file_put_contents($this->file, "<?php return ".var_export($dump, true).";\n");
        return $dump;
    }
    public function clearFile(){
        if (LocalFs::file_exists($this->file))
            LocalFs::unlink($this->file);
    }

    public function loadFromFile(){
        try {
            @$dump = include($this->file);
            if ($dump) {
                $this->load($dump);
            }
        } catch(\Exception $ex) {
            return false;
        }
        return true;
    }
    public function load($dump){
        $this->config = $dump['config'];
        $this->items = $dump['items'];
        $this->signature = $dump['signature'];
    }
    public function build(){
        $this->recursiveReadPath()->createSignature();
        return [
            'items'=>$this->items,
            'config'=>$this->config,
            'signature'=>$this->signature,
        ];
    }
    protected function recursiveReadPath () {
        $basePath = $this->config['basePath'];
        $paths = [];
        foreach( $this->config['paths'] as $path) {
            $rp = LocalFs::realpath($path);
            if ($rp===false) {
                $rp = LocalFs::realpath("$basePath".DIRECTORY_SEPARATOR."$path");
            }
            if ($rp===false) {
                // log that something is wrong in some assets path.
            } else {
                $paths[] = $rp;
            }
        }
        $paths = array_unique($paths);
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

        $alias = substr($itemPath, 0, strpos(":", $itemPath));
        if (array_key_exists($alias, $this->config['alias'])) {
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

        if (substr($itemPath,0,strlen($basePath))===$basePath) {
            $p = substr($itemPath,strlen($basePath)+1);
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
