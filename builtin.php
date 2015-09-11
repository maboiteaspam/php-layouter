<?php

function respondAsset ($f) {
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

    if (strpos($f, '.js')!==false) {
        header('Content-Type: application/x-javascript; charset=UTF-8');
    } else if (strpos($f, '.css')!==false) {
        header('Content-Type: text/css; charset=UTF-8');
    }

    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60*60*24*45)) . ' GMT');

    echo $content;
}
$reqUrl = $_SERVER['SCRIPT_FILENAME'];
if (file_exists($reqUrl)) {
    respondAsset($reqUrl);
    return;
} else if(file_exists('run/assets_path.php')) {
    $paths = include 'run/assets_path.php';
    $reqUrl = $_SERVER['PHP_SELF'];
    $d = dirname($reqUrl);
    $f = basename($reqUrl);
    foreach( $paths as $alias=>$path ){
        if ($alias===substr($d,0,strlen($alias))) {
            $f = str_replace($alias, $path, $reqUrl);
            if (file_exists($f)) {
                respondAsset($f);
                return;
            }
        }
    }
}


include(__DIR__.'/index.php');
