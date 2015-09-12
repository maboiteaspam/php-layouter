<?php

namespace C\FS;

class Registry {

    public $signature;

    public $config = [
        'basePath' => '/', // must always be an absolute path
        'paths' => [],
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

    public function __construct ($config=[]) {
        $this->config = array_merge($this->config, $config);
        $this->items = [];
    }

    public function build(){
        $this->recursiveReadPath();
        return [
            'items'=>$this->items,
            'signature'=>$this->signature,
        ];
    }
    public function load($dump){
        $this->items = $dump['items'];
        $this->signature = $dump['signature'];
    }
    public function registerPath($path){
        $this->config['paths'][] = $path;
        return $path;
    }
    public function setBasePath($path){
        $this->config['basePath'] = $path;
        return $path;
    }

    public function saveToFile($file){
        $this->sign(null);
        $dump = $this->build();
        file_put_contents($file, "<?php return ".var_export($dump, true).";\n");
        return $dump;
    }

    public function loadFromFile($file){
        try {
            @$dump = include($file);
            if ($dump) {
                $this->load($dump);
            }
        } catch(\Exception $ex) {
            return false;
        }
        return true;
    }

    public function sign($some=null){
        $signature = '';
        $this->each(function ($item, $localPath) use(&$signature, $some) {
            if (!$some || in_array($localPath, $some) || in_array($item['absolute_path'], $some) )
                $signature = sha1($item['sha1']);
        });
        return $signature;
    }

    public function each ($callback) {
        $basePath = $this->config['basePath'];
        foreach($this->items as $i=>$item) {
            $item['absolute_path'] = "$basePath/".$item['dir'].$item['name'];
            $callback($item, $i);
        }
    }
    public function get($itemPath){
        $basePath = $this->config['basePath'];
        if (isset($this->items[$itemPath])) {
            $item = $this->items[$itemPath];
            $item['absolute_path'] = "$basePath/".$item['dir'].$item['name'];
            return $item;
        }
        if (isset($this->items["$itemPath/"])) {
            $item = $this->items["$itemPath/"];
            $item['absolute_path'] = "$basePath/".$item['dir'].$item['name'];
            return $item;
        }
        foreach( $this->config['paths'] as $i=>$path) {
            $p = substr($itemPath, 0, strlen($path));
            if (in_array($p, $this->config['paths'])) {
                $itemP = substr($itemPath, strlen($basePath)+1);
                if (isset($this->items[$itemP])) {
                    $item = $this->items[$itemP];
                    $item['absolute_path'] = "$basePath/".$item['dir'].$item['name'];
                    return $item;
                }
            }
        }
        foreach( $this->config['paths'] as $i=>$path) {
            $p = rp("$basePath/$itemPath");
            $itemP = substr($p, strlen($basePath)+1);
            if (isset($this->items[$itemP])) {
                $item = $this->items[$itemP];
                $item['absolute_path'] = "$basePath/".$item['dir'].$item['name'];
                return $item;
            }
        }
        return false;
    }
    protected function recursiveReadPath () {
        $basePath = $this->config['basePath'];
        $paths = [];
        foreach( $this->config['paths'] as $path) {
            $rp = realpath($path);
            if ($rp===false) {
                $rp = realpath("$basePath/$path");
            }
            if ($rp===false) {
                // log that something is wrong in some assets path.
            } else {
                $paths[] = $rp;
            }
        }
        $paths = array_unique($paths);
        foreach( $paths as $path) {
            $Directory = new \RecursiveDirectoryIterator($path);
            $filter = new \RecursiveCallbackFilterIterator($Directory, function ($current, $key, $iterator) {
                if (in_array($current->getFilename(), ['..'])) {
                    return FALSE;
                }
                return $current;
            });
            $Iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);

            foreach( $Iterator as $Iterated ) {
                /* @var $Iterated \SplFileInfo */
                $this->addItem($Iterated);
            }
        }
    }
    public function addItem ($path) {
        $basePath = $this->config['basePath'];

        if (is_string($path)) {
            $path = new \SplFileInfo($path);
        }
        /* @var $path \SplFileInfo */

        $fp = substr($path->getRealPath(), strlen($basePath)+1);
        $p = dirname($fp)."/";
        $item = [
            'type'          => $path->isFile()?'file':'dir',
            'name'          => $path->getFilename()==='.'?basename($fp):$path->getFilename(),
            'dir'           => $p,
            'sha1'          => $path->isFile()?sha1(file_get_contents($path->getRealPath())):'',
            'extension'     => $path->getExtension(),
            'file_mtime'    => $path->getMTime(),
            'file_atime'    => $path->getATime(),
            'file_ctime'    => $path->getCTime(),
        ];

        $key = $fp.($path->isFile()?'':'/');
        $this->items[$key] = $item;
    }
    public function removeItem ($path) {
        $basePath = $this->config['basePath'];
        if (is_string($path)) {
            $path = new \SplFileInfo($path);
        }
        $fp = substr($path->getRealPath(), strlen($basePath)+1);
        $key = $fp.($path->isFile()?'':'/');
        unset($this->items[$key]);
    }
    public function refreshItem ($path) {
        $this->addItem($path);
    }
}

function rp($path) {
    $out=array();
    foreach(explode('/', $path) as $i=>$fold){
        if ($fold=='' || $fold=='.') continue;
        if ($fold=='..' && $i>0 && end($out)!='..') array_pop($out);
        else $out[]= $fold;
    } return ($path{0}=='/'?'/':'').join('/', $out);
}
