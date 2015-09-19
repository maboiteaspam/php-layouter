<?php
namespace C\HttpCache;

use C\FS\LocalFs;

class Store{

    public $path;

    public function setStorePath ($path) {
        $this->path = $path;
    }

    public function store (TaggedResource $resource, $content) {
        $etag = $resource->originalTag;
        LocalFs::file_put_contents("$this->path/resource-$etag.php",
            "<?php return ".var_export(serialize($resource), true).";");
        LocalFs::file_put_contents("$this->path/content-$etag.php",
            "<?php return ".var_export(serialize($content), true).";");

    }

    public function getResource ($etag) {
        $f = "$this->path/resource-$etag.php";
        if (LocalFs::file_exists($f)) {
            return unserialize(include($f));
        }
        return false;
    }

    public function getContent ($etag) {
        $f = "$this->path/content-$etag.php";
        if (LocalFs::file_exists($f)) {
            return unserialize(include($f));
        }
        return false;
    }
}