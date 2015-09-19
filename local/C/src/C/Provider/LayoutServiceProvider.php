<?php
namespace C\Provider;

use C\FS\LocalFs;
use C\Misc\Utils;
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
        LocalFs::$record = $app['debug'];

        $app['layout'] = $app->share(function() use($app) {
            $helpers = [
                'urlFor'=> function ($name, $options=[], $only=[]) use(&$app) {
                    $options = Utils::arrayPick($options, $only);
                    return $app['url_generator']->generate($name, $options);
                },
                'urlArgs'=> function ($data=[], $only=[]) use(&$app) {
                    /* @var $block \C\LayoutBuilder\Layout\Block */
                    $block = $this;
                    if (isset($block->meta['from'])) {
                        $data = array_merge(Utils::arrayPick($block->meta, ['from']), $data);
                    }
                    $data = Utils::arrayPick($data, $only);
                    $query = http_build_query($data);
                    return $query ? '?'.$query : '';
                }
            ];
            $layout = new Layout([
                'debug'         => $app['debug'],
                'helpers'       => $helpers,
                'imgUrls'       => [],
            ]);
            if (isset($app['dispatcher'])) $layout->setDispatcher($app['dispatcher']);
            return $layout;
        });


        $app['layout.responder'] = $app->protect(function ($response) use ($app) {
            $request = $app['request'];
            /* @var $request \Symfony\Component\HttpFoundation\Request */
            /* @var $response \Symfony\Component\HttpFoundation\Response */

            if (isset($app['httpcache.tagger'])) {
                $TaggedResource = $app['layout']->getTaggedResource();
                $etag = $app['httpcache.tagger']->sign($TaggedResource);
                $app['httpcache.taggedResource'] = $TaggedResource;
                $response->setETag($etag);

                // this is super important to get etag working properly.
                $response->setProtocolVersion('1.1');
//            $response->mustRevalidate(true);
//            $response->setPrivate(true);

                if ($response->isNotModified($request)) {
                    return $response;
                }
            }


            $response->setContent($app['layout']->render());

            return $response;
        });
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
        if ($app['assets.fs']) {
            $app['assets.fs']->register(__DIR__.'/../jQueryLayoutBuilder/templates/');
            $app['assets.fs']->register(__DIR__.'/../HTMLLayoutBuilder/templates/');
            $app['assets.fs']->register(__DIR__.'/../Dashboard/templates/');
            $app['assets.fs']->register(__DIR__.'/../jQueryLayoutBuilder/assets/');
            $app['assets.fs']->register(__DIR__.'/../Dashboard/assets/');
        }
    }
}