<?php

namespace C\BlogData;

use Silex\Application;
use Silex\ServiceProviderInterface;

class BlogDataServiceProvider implements ServiceProviderInterface
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
        if ($app['capsule.schema']) {
            $app['capsule.schema']->register(new Schema);
        }
    }
}