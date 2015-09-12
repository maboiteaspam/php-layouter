<?php
$projectPath = __DIR__;
$staticAsset = include('local/C/src/C/Foundation/builtin.php');

if (!$staticAsset) {
    include(__DIR__.'/index.php');
}
die();