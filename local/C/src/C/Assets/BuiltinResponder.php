<?php
namespace C\Assets;

class BuiltinResponder {
    /**
     * @var \C\FS\KnownFs
     */
    public $fs;
    public function setFS ($fs) {
        $this->fs = $fs;
    }
    public function sendAsset ($item) {

        $f = $item['absolute_path'];
        $extension = $item['extension'];

        $content = file_get_contents($f);

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $if_modified_since = preg_replace('/;.*$/', '',   $_SERVER['HTTP_IF_MODIFIED_SINCE']);
        } else {
            $if_modified_since = '';
        }
        $mtime = filemtime($f);
        $gmdate_mod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
        if ($if_modified_since == $gmdate_mod) {
            header("HTTP/1.0 304 Not Modified");
            exit;
        }
        header("Last-Modified: $gmdate_mod");

        if ($extension==="js") {
            header('Content-Type: application/x-javascript; charset=UTF-8');
        } else if ($extension==="css") {
            header('Content-Type: text/css; charset=UTF-8');
        }

        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60*60*24*45)) . ' GMT');

        return $content;
    }

    /**
     * @return bool
     */
    public function respond () {
        $reqUrl = $_SERVER['PHP_SELF'];
        $acceptableAssets = ['jpeg','jpg','png','gif','css','js'];
        $extension = substr(strrchr($reqUrl, "."), 1);
        if (in_array($extension, $acceptableAssets)) {
            $item = $this->fs->get($reqUrl);
            if ($item) {
                echo $this->sendAsset($item);
            } else {
                header("HTTP/1.0 404 Not Found");
            }
            die();// so dirty.
        }
    }
}