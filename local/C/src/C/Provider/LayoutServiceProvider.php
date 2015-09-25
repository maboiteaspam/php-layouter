<?php
namespace C\Provider;

use C\FS\LocalFs;
use C\Layout\Transforms;
use C\Layout\Layout;

use C\View\CommonViewHelper;
use C\View\Env;
use C\View\LayoutViewHelper;
use C\View\RoutingViewHelper;
use C\View\FormViewHelper;
use C\View\Context;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
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
            $layout = new Layout();
            if ($app['debug']) $layout->enableDebug(true);
            if (isset($app['dispatcher'])) $layout->setDispatcher($app['dispatcher']);
            $layout->setContext($app['layout.view']);

            $app['layout.helper.layout']->setLayout($layout);
            $app['layout.view']->addHelper($app['layout.helper.layout']);
            $app['layout.view']->addHelper($app['layout.helper.common']);
            $app['layout.view']->addHelper($app['layout.helper.routing']);
            $app['layout.view']->addHelper($app['layout.helper.form']);

            return $layout;
        });

        $app['layout.helper.layout'] = $app->share(function () use($app) {
            $layoutViewHelper = new LayoutViewHelper();
            $layoutViewHelper->setEnv($app['layout.env']);
            return $layoutViewHelper;
        });

        $app['layout.helper.common'] = $app->share(function () use($app) {
            $commonHelper = new CommonViewHelper();
            $commonHelper->setEnv($app['layout.env']);
            // see more about translator here http://stackoverflow.com/questions/25482856/basic-use-of-translationserviceprovider-in-silex
            $commonHelper->setTranslator($app['translator']);
            return $commonHelper;
        });

        $app['layout.helper.routing'] = $app->share(function () use($app) {
            $routingHelper = new RoutingViewHelper();
            $routingHelper->setEnv($app['layout.env']);
            $routingHelper->setUrlGenerator($app["url_generator"]);
            return $routingHelper;
        });

        $app['layout.helper.form'] = $app->share(function () use($app) {
            $formHelper = new FormViewHelper();
            $formHelper->setEnv($app['layout.env']);
            $formHelper->setCommonHelper($app['layout.helper.common']);
            return $formHelper;
        });

        $app['layout.translator.available_languages'] = ['en', 'fr'];
        $app['layout.env.charset'] = 'utf-8';
        $app['layout.env.date_format'] = '';
        $app['layout.env.timezone'] = '';
        $app['layout.env.number_format'] = '';
        $app['layout.env'] = $app->share(function() use($app) {
            $env = new Env();
            $env->setCharset($app['layout.env.charset']);
            $env->setDateFormat($app['layout.env.date_format']);
            $env->setTimezone($app['layout.env.timezone']);
            $env->setNumberFormat($app['layout.env.number_format']);
            return $env;
        });

        $app['layout.view'] = $app->share(function() use($app) {
            return  new Context();
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
     *
     * @param Application $app Silex application instance.
     *
     * @return void
     **/
    public function boot(Application $app)
    {
        $app->before(function (Request $request) use ($app) {
            $app['translator']->setLocale(
                $request->getPreferredLanguage($app['layout.translator.available_languages'])
            );
        });
    }
}
