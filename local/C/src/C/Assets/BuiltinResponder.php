<?php
namespace C\Assets;

use C\FS\LocalFs;
use C\Misc\Utils;

class BuiltinResponder {
    /**
     * @var string
     */
    public $wwwDir;
    /**
     * @var \C\FS\KnownFs
     */
    public $fs;
    public function setFS ($fs) {
        $this->fs = $fs;
    }
    public function sendAsset ($f, $extension) {

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
            return null;
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
    public function respond ($verbose=false) {
        $reqUrl = $_SERVER['PHP_SELF'];
        $acceptableAssets = ['jpeg','jpg','png','gif','css','js'];
        $extension = substr(strrchr($reqUrl, "."), 1);
        if (in_array($extension, $acceptableAssets)) {
            $item = $this->fs->get($reqUrl);
            if ($item) {
                echo $this->sendAsset($item['absolute_path'], $item['extension']);
                if ($verbose) Utils::stdout("served $reqUrl");
            } else if (LocalFs::file_exists("{$this->wwwDir}{$reqUrl}")) {
                echo $this->sendAsset("{$this->wwwDir}{$reqUrl}", strpos('js', $reqUrl)===false?'css':'js');
                if ($verbose) Utils::stdout("served $reqUrl");
            } else {
                header("HTTP/1.0 404 Not Found");
                if ($verbose) Utils::stdout("missed $reqUrl");
            }
            exit;
        }
    }
}