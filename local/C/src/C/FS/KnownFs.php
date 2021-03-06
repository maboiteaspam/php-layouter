<?php

namespace C\FS;

/**
 * Class KnownFs
 *
 * A cache-try-passtrough FS.
 * Give it a base path,
 * Register absolute Paths,
 * Use it to execute the File System actions.
 *
 * If the cache is generated and exists,
 * It will use it as much as possible,
 * If the cache is missing, or the file is unreferenced,
 * it passes-trough,
 *
 * Using the public property $fs,
 * review and analyze all non cached file systems calls
 * and get rid of them.
 *
 * @package C\FS
 */
class KnownFs {

    /**
     * @var Registry
     */
    public $registry;
    /**
     * @var LocalFs
     */
    public $fs;

    public function __construct (Registry $registry) {
        $this->registry = $registry;
        $this->fs = new LocalFs();
    }

    public function register ($path, $as=null) {
        $this->registry->registerPath($path, $as);
    }

    public function setBasePath ($bp) {
        $this->registry->setBasePath($bp);
    }

    public function getBasePath () {
        return $this->registry->getBasePath();
    }

    public function setFS ($fs) {
        $this->fs = $fs;
    }



    public function get ($path) {
        return $this->registry->get($path);
    }

    public function mkdir ($dir) {
        $knownItem = $this->registry->get($dir);
        if ($knownItem!==false) {
            return true;
        }
        $res = call_user_func_array([$this->fs, 'mkdir'], func_get_args());
        if ($res) {
            $this->registry->addItem($dir);
        }
    }

    public function rmdir ($dir) {
        $knownItem = $this->registry->get($dir);
        if ($knownItem===false) {
            return false;
        }
        $res = call_user_func_array([$this->fs, 'rmdir'], func_get_args());
        if ($res) {
            $this->registry->removeItem($dir);
        }
    }

    public function is_dir ($dir) {
        $knownItem = $this->registry->get($dir);
        if ($knownItem!==false) {
            return true;
        }
        $res = call_user_func_array([$this->fs, 'is_dir'], func_get_args());
        if ($res) {
            $this->registry->addItem($dir);
        }
        return $res;
    }

    public function touch ($path) {
        $res = call_user_func_array([$this->fs, 'touch'], func_get_args());
        if ($res) {
            $this->registry->addItem($path);
        }
        return $res;
    }

    public function unlink ($path) {
        $knownItem = $this->registry->get($path);
        if ($knownItem===false) {
            return false;
        }
        $res = call_user_func_array([$this->fs, 'unlink'], func_get_args());
        if ($res) {
            $this->registry->removeItem($path);
        }
        return $res;
    }

    public function file_exists ($path) {
        $knownItem = $this->registry->get($path);
        if ($knownItem!==false) {
            return true;
        }
        $res = call_user_func_array([$this->fs, 'file_exists'], func_get_args());
        if ($res) {
            $this->registry->addItem($path);
        }
        return $res;
    }

    public function file_get_contents ($path) {
        $knownItem = $this->registry->get($path);
        if ($knownItem===false) {
            return false;
        }
        if ($knownItem['type']!=='file') {
            return false;
        }
        $res = call_user_func_array([$this->fs, 'file_get_contents'], func_get_args());
        return $res;
    }

    public function file_put_contents ($path) {
        $res = call_user_func_array([$this->fs, 'file_put_contents'], func_get_args());
        if ($res) {
            $this->registry->addItem($path);
        }
        return $res;
    }

    public function realpath ($path) {
        $knownItem = $this->registry->get($path);
        if ($knownItem!==false) {
            return $knownItem['absolute_path'];
        }
        return call_user_func_array([$this->fs, 'realpath'], func_get_args());
    }

    public function file_mtime ($path) {
        $knownItem = $this->registry->get($path);
        if ($knownItem===false) {
            return false;
        }
        return $knownItem['file_mtime'];
    }

    public function file_atime ($path) {
        $knownItem = $this->registry->get($path);
        if ($knownItem===false) {
            return false;
        }
        return $knownItem['file_atime'];
    }

    public function file_ctime ($path) {
        $knownItem = $this->registry->get($path);
        if ($knownItem===false) {
            return false;
        }
        return $knownItem['file_ctime'];
    }

    public function sha1 ($path) {
        $knownItem = $this->registry->get($path);
        if ($knownItem===false) {
            return false;
        }
        return $knownItem['sha1'];
    }
}