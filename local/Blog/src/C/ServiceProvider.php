<?php

namespace C\Blog;

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
    }
}