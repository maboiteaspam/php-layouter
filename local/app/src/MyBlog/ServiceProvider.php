<?php

namespace MyBlog;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {

    }
    /**
     *
     * @param Application $app Silex application instance.
     *
     * @return void
     **/
    public function boot(Application $app)
    {
        if ($app['assets.fs']) {
            $app['assets.fs']->register(__DIR__.'/assets/');
            $app['assets.fs']->register(__DIR__.'/templates/');
        }

        if (isset($app['layout'])) {
            $app['layout']->registerImgPattern('blog_detail', '/images/blog/detail/:id.jpg');
            $app['layout']->registerImgPattern('blog_list', '/images/blog/list/:id.jpg');
        }
    }
}