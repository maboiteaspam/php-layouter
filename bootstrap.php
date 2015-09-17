<?php
$wwwPath = __DIR__."/www/";
$staticAsset = include('local/C/src/C/Foundation/builtin.php');

if (!$staticAsset) {
    $app = include(__DIR__.'/index.php');
    $app->run();
}
