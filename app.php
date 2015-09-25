<?php

$app = require("bootstrap.php");

$app->mount('/', $myBlogController);

$app->run();
