<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ModernAppServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
    }
    /**
     * Boot the Capsule service.
     *
     * @param Application $app Silex application instance.
     *
     * @return void
     **/
    public function boot(Application $app)
    {
        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(__DIR__.'/../ModernApp/Dashboard/assets/', 'Dashboard');
            $app['assets.fs']->register(__DIR__.'/../ModernApp/jQuery/assets/', 'jQuery');
        }
        if (isset($app['layout.fs'])) {
            $app['layout.fs']->register(__DIR__.'/../ModernApp/HTML/templates/', 'HTML');
            $app['layout.fs']->register(__DIR__.'/../ModernApp/Dashboard/templates/', 'Dashboard');
            $app['layout.fs']->register(__DIR__.'/../ModernApp/jQuery/templates/', 'jQuery');
        }
    }
}