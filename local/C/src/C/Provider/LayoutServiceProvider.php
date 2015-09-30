<?php
namespace C\Provider;

use C\FS\KnownFs;
use C\FS\LocalFs;
use C\FS\Registry;

use C\Layout\Layout;
use C\Layout\LayoutSerializer;
use C\Layout\RequestTypeMatcher;
use C\Misc\Utils;
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

        $app['layout'] = $app->share(function(Application $app) {
            $layout = new Layout();
            if ($app['debug']) $layout->enableDebug(true);
            if (isset($app['dispatcher'])) $layout->setDispatcher($app['dispatcher']);
            $layout->setContext($app['layout.view']);
            $layout->setFS($app['layout.fs']);

            $locales = $app['layout.translator.available_languages'];
            $request = $app['request'];
            $requestMatcher = new RequestTypeMatcher();
            $requestMatcher->setRequest($request);
            $requestMatcher->setLang($request->getPreferredLanguage($locales));
            $layout->setRequestMatcher($requestMatcher);

            $layout->setLayoutSerializer($app['layout.serializer']);

            $app['layout.helper.layout']->setLayout($layout);
            $app['layout.view']->addHelper($app['layout.helper.layout']);
            $app['layout.view']->addHelper($app['layout.helper.common']);
            $app['layout.view']->addHelper($app['layout.helper.routing']);
            $app['layout.view']->addHelper($app['layout.helper.form']);

            return $layout;
        });

        $app['layout.helper.layout'] = $app->share(function (Application $app) {
            $layoutViewHelper = new LayoutViewHelper();
            $layoutViewHelper->setEnv($app['layout.env']);
            return $layoutViewHelper;
        });

        $app['layout.helper.common'] = $app->share(function (Application $app) {
            $commonHelper = new CommonViewHelper();
            $commonHelper->setEnv($app['layout.env']);
            // see more about translator here http://stackoverflow.com/questions/25482856/basic-use-of-translationserviceprovider-in-silex
            if (isset($app['translator'])) {
                $commonHelper->setTranslator($app['translator']);
            }
            return $commonHelper;
        });

        $app['layout.helper.routing'] = $app->share(function (Application $app) {
            $routingHelper = new RoutingViewHelper();
            $routingHelper->setEnv($app['layout.env']);
            $routingHelper->setUrlGenerator($app["url_generator"]);
            return $routingHelper;
        });

        $app['layout.helper.form'] = $app->share(function (Application $app) {
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
        $app['layout.env'] = $app->share(function(Application $app) {
            $env = new Env();
            $env->setCharset($app['layout.env.charset']);
            $env->setDateFormat($app['layout.env.date_format']);
            $env->setTimezone($app['layout.env.timezone']);
            $env->setNumberFormat($app['layout.env.number_format']);
            return $env;
        });

        $app['layout.view'] = $app->share(function() {
            return new Context();
        });

        $app['layout.responder'] = $app->protect(function (Response $response) use ($app) {
            $request = $app['request'];
            /* @var $request \Symfony\Component\HttpFoundation\Request */
            /* @var $layout Layout */
            $layout = $app['layout'];

            $layout->emit('controller_build_finish');

            // esi support
            // https://www.varnish-cache.org/trac/wiki/ESIfeatures
            // https://www.varnish-software.com/book/3/Content_Composition.html#edge-side-includes
            // https://www.varnish-cache.org/docs/3.0/tutorial/esi.html
            // http://blog.lavoie.sl/2013/08/varnish-esi-and-cookies.html
            // http://symfony.com/doc/current/cookbook/cache/varnish.html
            // http://silex.sensiolabs.org/doc/providers/http_cache.html
            // https://github.com/serbanghita/Mobile-Detect

//            $layoutRequestKinds = $layout->collectRequestKinds();
//            if (in_array('esi', $layoutRequestKinds)) {
//                if ($request->headers->has('do_esi')) {
//                    // it means the server is doing an esi request
//                    // only a fragment of app should be rendered
//                } else {
//                    // mean it is not an esi request.
//                    // as the layout claim to be using
//                    // esi support, we should set headers
//                    // to enable esi support on reverse proxy
//                    $request->headers->set('X-Esi','1');
//                }
//
//            }

            $content = $layout->render();
            Utils::stderr('response is new '.$request->getUri());

            if (isset($app['httpcache.tagger'])) {
                $TaggedResource = $layout->getTaggedResource();
                if ($TaggedResource===false) {
                    Utils::stderr('this layout prevents caching');
                    // this layout contains resource which prevent from being cached.
                    // we shall not let that happen.
                } else {
                    $TaggedResource->addResource($app['env']);
                    $TaggedResource->addResource($app['debug']?'with-debug':'without-debug');
                    $etag = $app['httpcache.tagger']->sign($TaggedResource);
                    $app['httpcache.taggedResource'] = $TaggedResource;
                    $response->setETag($etag);
                    $response->setProtocolVersion('1.1');

                    $response->setPublic(true);
                    $response->mustRevalidate(true);
//                    $response->setMaxAge(60*10);
                }

            }

            if (!$response->isNotModified($request)) {
                Utils::stderr('response is modified '.$request->getUri());
                $response->setContent($content);
            }

            return $response;
        });

        $app['layout.serializer'] = $app->share(function (Application $app) {
            // @todo split across service providers
            $serializer = new LayoutSerializer();
            $serializer->setAssetsFS($app["assets.fs"]);
            $serializer->setLayoutFS($app["layout.fs"]);
            $serializer->setModernFS($app["modern.fs"]);
            return $serializer;
        });

        if (!isset($app['layout.cache_store_name']))
            $app['layout.cache_store_name'] = "layout-store";

        $app['layout.fs'] = $app->share(function(Application $app) {
            $storeName = $app['layout.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            return new KnownFs(new Registry('layout-', $cache, [
                'basePath' => $app['project.path']
            ]));
        });

        if (isset($app['form.extensions'])) {
            $app['form.extensions'] = $app->share($app->extend('form.extensions', function ($extensions) use ($app) {
//                $extensions[] = new CoreExtension();
                return $extensions;
            }));
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
        $app->before(function (Request $request) use ($app) {
            $app['layout.fs']->registry->loadFromCache();
            if (isset($app['translator'])) {
                $app['translator']->setLocale(
                    $request->getPreferredLanguage($app['layout.translator.available_languages'])
                );
            }
        });

        if (isset($app['httpcache.tagger'])) {
            $fs = $app['layout.fs'];
            $tagger = $app['httpcache.tagger'];
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->tagDataWith('template', function ($file) use($fs) {
                $template = $fs->get($file);
                $h = '';
                if ($template) {
                    $h .= $template['sha1'].$template['dir'].$template['name'];
                } else if(LocalFs::file_exists($file)) {
                    $h .= LocalFs::file_get_contents($file);
                } else {
                    // that is bad, it means we have registered files
                    // that does not exists
                    // or that can t be located back.
                    Utils::stderr('----: '.var_export($template, true));
                    Utils::stderr('----: '.var_export($fs->registry->config, true));
                    Utils::stderr('----: '.$file);
                }
                return $h;
            });
        }
    }
}
