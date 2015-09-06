<?php

$reqUrl = $_SERVER['SCRIPT_FILENAME'];
if (file_exists($reqUrl)) {
    $content = file_get_contents($reqUrl);


    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $if_modified_since = preg_replace('/;.*$/', '',   $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    } else {
        $if_modified_since = '';
    }
    $mtime = filemtime($_SERVER['SCRIPT_FILENAME']);
    $gmdate_mod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    if ($if_modified_since == $gmdate_mod) {
        header("HTTP/1.0 304 Not Modified");
        exit;
    }
    header("Last-Modified: $gmdate_mod");

    if (strpos($reqUrl, '.js')!==false) {
        header('Content-Type: application/x-javascript; charset=UTF-8');
    } else if (strpos($reqUrl, '.css')!==false) {
        header('Content-Type: text/css; charset=UTF-8');
    }

    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60*60*24*45)) . ' GMT');

    echo $content;

    return;
}


include(__DIR__.'/index.php');
