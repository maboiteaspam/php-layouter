<?php
return [
    'debug' => true,

    'server_type' => 'builtin',
    'projectPath' => "%projectPath%",
    'documentRoot' => '%projectPath%/www/',

    'private_build_dir' => '%projectPath%/run/',
    'public_build_dir' => '%projectPath%/www/run/',

//    'blogdata.provider' => "Eloquent",
    'blogdata.provider' => "PO",

    'httpcache.check_taged_resource_freshness' => !false,

    'assets.concat' => false,
    'assets.fs_file_path' => '%projectPath%/run/assets_fs_cache.php',
    'assets.bridge_file_path' => '%projectPath%/run/bridge.php',

    'capsule.schema_file_cache' => '%projectPath%/run/schemas.php',
    'capsule.connections' => [
        "default"=>[
//            'driver'   => 'sqlite',
//            'database' => '%projectPath%/run/database.sqlite',
//        'database' => ':memory:',

        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'database'  => 'blog',
        'username'  => 'root',
        'password'  => '',

            'prefix'   => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        "prod"=>[
            'driver'   => 'sqlite',
//            'database' => '%projectPath%/run/database.sqlite',
            'database' => ':memory:',
            'prefix'   => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
    ],
];