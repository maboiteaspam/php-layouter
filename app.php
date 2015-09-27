<?php

$app = require("bootstrap.php");

$app->mount('/', $myBlogController);
$app->mount('/form', $formDemo);

$app->run();
