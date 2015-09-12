<?php

namespace MyBlog;

use \C\Module as CModule;

class Module extends CModule{
    public function register($app) {
        if (isset($options['templatesFS'])) {
            $options['templatesFS']->register(__DIR__.'/templates/');
        }
        if (isset($app['assetsFS'])) {
            $app['assetsFS']->register(__DIR__.'/assets/');
        }
        if (isset($app['layout'])) {
            $app['layout']->registerImgPattern('blog_detail', '/images/blog/detail/:id.jpg');
            $app['layout']->registerImgPattern('blog_list', '/images/blog/list/:id.jpg');
        }
        if (isset($app['capsule'])) {
            $env = $app['env'];
            $settings = $app["capsule.settings.$env"];
            $app["capsule.connection.$env"] = $app->protect(function () use (&$app, &$settings) {
                /* @var $capsule \Illuminate\Database\Capsule\Manager */
                $app['capsule']->addConnection($settings);
            });
        }
    }
}