<?php
namespace C\Provider;

use C\FS\LocalFs;
use C\Misc\Utils;
use C\FS\KnownFs;
use C\FS\Registry;
use C\LayoutBuilder\Layout\Layout;
use Silex\Application;
use Silex\ServiceProviderInterface;

class LayoutServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        $app['layout.fs'] = $app->share(function() use($app) {
            return new KnownFs(new Registry($app['layout.cache_path']."/layout_fs.php"));
        });
        $app['layout'] = $app->share(function() use($app) {
            $helpers = [
                'urlFor'=> function ($name, $options=[], $only=[]) use(&$app) {
                    $options = Utils::arrayPick($options, $only);
                    return $app['url_generator']->generate($name, $options);
                },
                'urlArgs'=> function ($data=[], $only=[]) use(&$app) {
                    $block = $this;
                    if (isset($block->meta['from'])) {
                        $data = array_merge(Utils::arrayPick($block->meta, ['from']), $data);
                    }
                    $data = Utils::arrayPick($data, $only);
                    $query = http_build_query($data);
                    return $query ? '?'.$query : '';
                }
            ];
            return new Layout([
                'debug'         => $app['debug'],
                'dispatcher'    => $app['dispatcher'],
                'helpers'       => $helpers,
                'imgUrls'       => [],
            ]);
        });


        LocalFs::$record = $app['debug'];

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
        $app['layout.fs']->registry->loadFromFile();
        if ($app["env"]==="dev") {
            $app->before(function() use($app) {
                $app['layout.fs']->registry->saveToFile();
            }, Application::LATE_EVENT);
        }

    }
}