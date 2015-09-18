<?php

namespace C\Blog;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ControllerProviderInterface;

class MyBlogControllersProvider implements
    ServiceProviderInterface,
    ControllerProviderInterface
{
    /**
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        //
        // Define controller services
        //

        $app['myblog.controllers'] = $app->share(function() use ($app) {
            return new Controller();
        });
    }
    /**
     *
     * @param Application $app Silex application instance.
     * @return void
     **/
    public function boot(Application $app)
    {
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'myblog.controllers:home')
            ->method('GET')
            ->bind('blog.home');

        return $controllers;
    }
}