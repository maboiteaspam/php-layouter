<?php

namespace C\Blog;

use Silex\Application;
use Silex\ServiceProviderInterface;

class BlogServiceProvider implements ServiceProviderInterface
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
    }
}