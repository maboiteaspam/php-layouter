<?php

namespace MyBlog;

use C\Blog\Schema as BlogSchema;
use \C\Module as CModule;

class Module extends CModule{
    public function register($app) {
        if (isset($app['assetsFS'])) {
            $app['assetsFS']->register(__DIR__.'/assets/');
        }
        if (isset($app['schema_loader'])) {
            $app['schema_loader']->register(new BlogSchema);
        }
        if (isset($app['layout'])) {
            $app['layout']->registerImgPattern('blog_detail', '/images/blog/detail/:id.jpg');
            $app['layout']->registerImgPattern('blog_list', '/images/blog/list/:id.jpg');
        }
        if (isset($app['capsule'])) {
            $database = $app['private_build_dir'].'database.sqlite';
            $exists = file_exists($database);
            $app['capsule.exists'] = $app->protect(function () use ($exists) {
                return $exists;
            });
            $app['capsule.delete'] = $app->protect(function () use (&$database) {
                return unlink($database);
            });
            $capsule = $app['capsule'];
            $app['capsule.connection.dev'] = $app->protect(function () use (&$capsule, &$database) {
                touch($database);
                /* @var $capsule \Illuminate\Database\Capsule\Manager */
                $capsule->addConnection([
                    'driver'   => 'sqlite',
                    'database' => $database,
                    'prefix'   => '',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                ], 'default');
            });
        }
    }
}