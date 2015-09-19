<?php
namespace C\Provider;

use C\FS\LocalFs;
use C\Misc\Utils;
use C\LayoutBuilder\Layout\Layout;

use Silex\Application;
use Silex\ServiceProviderInterface;

class CServiceProvider implements ServiceProviderInterface
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
        $app['dispatcher']->dispatch("boot_done");
        $app['dispatcher']->dispatch("before_app_start");
    }
}