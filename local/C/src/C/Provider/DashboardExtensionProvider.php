<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use C\ModernApp\DashboardExtension\Transforms;

class DashboardExtensionProvider implements ServiceProviderInterface
{
    /**
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        if (isset($app['modern.dashboard.extensions'])) {
            $app['modern.dashboard.extensions'] = $app->extend('modern.dashboard.extensions', function ($extensions) use($app) {
                $extensions[] = Transforms::transform($app);
                return $extensions;
            });
        }
    }
    /**
     *
     * @param Application $app Silex application instance.
     *
     * @return void
     **/
    public function boot(Application $app)
    {
        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(__DIR__.'/../ModernApp/DashboardExtension/assets/', 'DashboardExtension');
        }
        if (isset($app['layout.fs'])) {
            $app['layout.fs']->register(__DIR__.'/../ModernApp/DashboardExtension/templates/', 'DashboardExtension');
        }
    }
}