<?php
return [
    'debug' => !true,
    'server_type' => 'builtin',
    'projectPath' => "%projectPath%",
    'documentRoot' => '%projectPath%/www/',
    'private_build_dir' => '%projectPath%/run/',
    'public_build_dir' => '%projectPath%/www/run/',
    'assets.concat' => false,
    'capsule.settings.dev' => [
        'driver'   => 'sqlite',
        'database' => '%projectPath%/run/database.sqlite',
//        'database' => ':memory:',
        'prefix'   => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'capsule.settings.prod' => [
        'driver'   => 'sqlite',
        'database' => '%projectPath%/run/database.sqlite',
        'prefix'   => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
];