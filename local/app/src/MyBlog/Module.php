<?php

namespace MyBlog;

use C\Blog\Schema as BlogSchema;
use \C\Module as CModule;

class Module extends CModule{
    public function register($options) {
        if (isset($options['assetsFS'])) {
            $options['assetsFS']->register(__DIR__.'/assets/');
        }
        if (isset($options['schema_loader'])) {
            $options['schema_loader']->register(new BlogSchema);
        }
        if (isset($options['layout'])) {
            $options['layout']->registerImgPattern('blog_detail', '/images/blog/detail/:id.jpg');
            $options['layout']->registerImgPattern('blog_list', '/images/blog/list/:id.jpg');
        }
        if (isset($options['capsule'])) {
            $database = $options['private_build_dir'].'database.sqlite';
            $exists = file_exists($database);
            $options['capsule.exists'] = function () use ($exists) {
                return $exists;
            };
            $options['capsule.delete'] = function () use (&$database) {
                return unlink($database);
            };
            $capsule = $options['capsule'];
            $options['capsule.connection.dev'] = function () use (&$capsule, &$database) {
                touch($database);
                /* @var $capsule \Illuminate\Database\Capsule\Manager */
                $capsule->addConnection([
                    'driver'   => 'sqlite',
                    'database' => $database,
                    'prefix'   => '',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                ], 'default');
            };
        }
    }
}