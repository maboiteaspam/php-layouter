<?php
$argv = isset($argv)?$argv:['', ''];
array_shift($argv);
$app = include(__DIR__.'/app.php');
if ($argv[0]=="--event") {
    $app->boot();
    $app['dispatcher']->dispatch($argv[1]);
} else {
    $app->run();
}
