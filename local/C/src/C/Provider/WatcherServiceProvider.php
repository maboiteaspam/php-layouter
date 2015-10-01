<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class WatcherServiceProvider implements ServiceProviderInterface
{
    /**
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        $app['watchers.watched'] = $app->share(function() {
            return [];
        });
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