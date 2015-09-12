<?php
$wwwPath = __DIR__."/www/";
$staticAsset = include('local/C/src/C/Foundation/builtin.php');

if (!$staticAsset) {
    include(__DIR__.'/index.php');
}
die();