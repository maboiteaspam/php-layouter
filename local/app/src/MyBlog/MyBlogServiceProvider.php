<?php

namespace C\Blog;

use Silex\Application;
use Silex\ServiceProviderInterface;

class MyBlogServiceProvider implements ServiceProviderInterface
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
        if ($app['layout.fs']) {
            $app['layout.fs']->register(__DIR__.'/assets/');
            $app['layout.fs']->register(__DIR__.'/templates/');
        }

        if (isset($app['layout'])) {
            $app['layout']->registerImgPattern('blog_detail', '/images/blog/detail/:id.jpg');
            $app['layout']->registerImgPattern('blog_list', '/images/blog/list/:id.jpg');
        }
    }
}