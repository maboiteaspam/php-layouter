<?php
namespace C\FormDemo;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ControllerProviderInterface;

class ControllersProvider implements
    ServiceProviderInterface,
    ControllerProviderInterface
{
    /**
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        $app['formdemo.controllers'] = $app->share(function() use ($app) {
            return  new Controllers();
        });
    }

    /**
     * @param Application $app Silex application instance.
     * @return void
     **/
    public function boot(Application $app)
    {
        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(__DIR__.'/assets/', 'FormDemo');
        }
        if (isset($app['layout.fs'])) {
            $app['layout.fs']->register(__DIR__.'/templates/', 'FormDemo');
        }
        if (isset($app['intl.fs'])) {
            $app['intl.fs']->register(__DIR__.'/intl/', 'FormDemo');
        }
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get( '/',
            $app['formdemo.controllers']->index()
        )->bind ('form_demo');
        $controllers->post( '/post',
            $app['formdemo.controllers']->submit()
        )->bind ('form_demo_post');


        return $controllers;
    }
}