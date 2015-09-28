<?php
return [
//    'debug' => true,

    'server_type' => 'builtin',
    'documentRoot' => '%project.path%/www/',

    'private_build_dir' => '%project.path%/run/',
    'public_build_dir' => '%project.path%/www/run/',

    'blogdata.provider' => "Eloquent",
//    'blogdata.provider' => "PO",


    'monolog.logfile'       => '%run.path%/development.log',

    'httpcache.check_taged_resource_freshness' => !false,

    'caches.options' => [
        'http-store'=>[
            'driver' => 'file',
            'cache_dir' => __DIR__ . '/run/http/',
        ],
        'assets-store'=>[
            'driver' => 'file',
            'cache_dir' => __DIR__ . '/run/assets/',
        ],
        'capsule-store'=>[
            'driver' => 'file',
            'cache_dir' => __DIR__ . '/run/capsule/',
        ],
        'layout-store'=>[
            'driver' => 'file',
            'cache_dir' => __DIR__ . '/run/layout/',
        ],
    ],
    'assets.concat' => false,
    'assets.patterns' => [
        'blog_detail' => '/images/blog/detail/:id.jpg',
        'blog_list' => '/images/blog/list/:id.jpg',
    ],
    'assets.fs_file_path' => '%project.path%/run/assets_fs_cache.php',
    'assets.bridge_file_path' => '%project.path%/run/bridge.php',

    'capsule.schema_file_cache' => '%project.path%/run/schemas.php',
    'capsule.connections' => [
        "default"=>[
            'driver'   => 'sqlite',
            'database' => '%project.path%/run/database.sqlite',
//        'database' => ':memory:',

//        'driver'    => 'mysql',
//        'host'      => '127.0.0.1',
//        'database'  => 'blog',
//        'username'  => 'root',
//        'password'  => '',

            'prefix'   => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        "prod"=>[
            'driver'   => 'sqlite',
//            'database' => '%project.path%/run/database.sqlite',
            'database' => ':memory:',
            'prefix'   => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
    ],
];