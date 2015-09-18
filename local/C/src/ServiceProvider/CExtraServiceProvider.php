<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class CExtraServiceProvider implements ServiceProviderInterface
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
            $app['layout.fs']->register(__DIR__.'/jQueryLayoutBuilder/templates/');
            $app['layout.fs']->register(__DIR__.'/HTMLLayoutBuilder/templates/');
            $app['layout.fs']->register(__DIR__.'/Dashboard/templates/');
            $app['layout.fs']->register(__DIR__.'/jQueryLayoutBuilder/assets/');
            $app['layout.fs']->register(__DIR__.'/Dashboard/assets/');
        }
    }
}