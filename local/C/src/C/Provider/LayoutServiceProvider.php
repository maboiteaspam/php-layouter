<?php
namespace C\Provider;

use C\FS\LocalFs;
use C\Layout\Transforms;
use C\Misc\Utils;
use C\Layout\Layout;

use C\View\AssetsViewHelper;
use C\View\CommonViewHelper;
use C\View\LayoutViewHelper;
use C\View\RoutingViewHelper;
use C\View\Context;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;

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
                    /* @var $block \C\Layout\Block */
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

        $app['layout.view_helpers'] = $app->share(function () {
            return [
                new CommonViewHelper(),
                new RoutingViewHelper(),
                new AssetsViewHelper(),
                new LayoutViewHelper(),
//                new FormViewHelper(),
            ];
        });

        $app['layout.view'] = $app->share(function() use($app) {
            $view = new Context();
            foreach($app['layout.view_helpers'] as $helper) {
                $view->addHelper($helper);
            }
            return $view;
        });

        $app['layout.responder'] = $app->protect(function (Response $response) use ($app) {
            $request = $app['request'];
            /* @var $request \Symfony\Component\HttpFoundation\Request */

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

        $app['layout.transforms'] = $app->share(function () use ($app) {
            $transforms = new Transforms($app['layout']);
            return $transforms;
        });

        $app['layout.html.transforms'] = $app->share(function () use ($app) {
            $transforms = new \C\ModernApp\HTML\Transforms($app['layout']);
            $transforms->setApp($app);
            $transforms->concatenateAssets($app['assets.concat']);
            $transforms->setAssetsFS($app['assets.fs']);
            $transforms->setDocumentRoot($app['documentRoot']);
            return $transforms;
        });

        $app['layout.jquery.transforms'] = $app->share(function () use ($app) {
            $transforms = new \C\ModernApp\jQuery\Transforms($app['layout']);
            return $transforms;
        });

        $app['layout.dashboard.transforms'] = $app->share(function () use ($app) {
            $transforms = new \C\ModernApp\Dashboard\Transforms($app['layout']);
            return $transforms;
        });

        $app['layout.static.transforms'] = $app->share(function () use ($app) {
            $transforms = new \C\StaticLayoutBuilder\Transforms($app['layout']);
            return $transforms;
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
    }
}
