<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class CacheProvider implements ServiceProviderInterface
{
    /**
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        if (isset($app['cache.drivers'])) {
            $app['cache.drivers'] = $app->extend('cache.drivers', function ($drivers) {
                $drivers['include'] = '\\C\\Cache\\IncludeCache';
                return $drivers;
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
    }
}