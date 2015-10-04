<?php
namespace C\Provider;

use C\FS\KnownFs;
use C\FS\LocalFs;
use C\FS\Registry;

use C\Layout\Layout;
use C\Layout\LayoutSerializer;
use C\Layout\RequestTypeMatcher;
use C\Misc\Utils;
use C\View\Env;
use C\View\Context;
use C\View\Helper\CommonViewHelper;
use C\View\Helper\LayoutViewHelper;
use C\View\Helper\RoutingViewHelper;
use C\View\Helper\FormViewHelper;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use C\Watch\WatchedRegistry;

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
            $requestMatcher->setLang($request->getPreferredLanguage($locales));
            $requestMatcher->setDevice('desktop');
            if (isset($app["mobile_detect"])) {
                if ($app["mobile_detect"]->isTablet()) {
                    $requestMatcher->setDevice('tablet');
                } elseif ($app["mobile_detect"]->isMobile()) {
                    $requestMatcher->setDevice('mobile');
                }
            }
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

            $layout->emit('controller_build_finish', $response);

            $content = $layout->render();
            Utils::stderr('response is new '.$request->getUri());

            $layout->emit('layout_build_finish', $response);

            $response->setProtocolVersion('1.1');

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

                    $response->setPublic(true);
                    $response->setSharedMaxAge(60);
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
            $serializer->setApp($app);
            if(isset($app["assets.fs"])) $serializer->setAssetsFS($app["assets.fs"]);
            if(isset($app["layout.fs"])) $serializer->setLayoutFS($app["layout.fs"]);
            if(isset($app["modern.fs"])) $serializer->setModernFS($app["modern.fs"]);
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

//        if (isset($app['form.extensions'])) {
            // @todo dig more about form framework...
//            $app['form.extensions'] = $app->share($app->extend('form.extensions', function ($extensions) use ($app) {
//                $extensions[] = new CoreExtension();
//                return $extensions;
//            }));
//        }
    }
    /**
     *
     * @param Application $app Silex application instance.
     *
     * @return void
     **/
    public function boot(Application $app)
    {
        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->extend('watchers.watched', function($watched, Application $app) {
                $w = new WatchedRegistry();
                $w->setRegistry($app['layout.fs']->registry);
                $w->setName("layout.fs");
                $watched[] = $w;
                return $watched;
            });
        }

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
                    //
                    // you may have forgotten somewhere
                    // $app['layout.fs']->register(__DIR__.'/path/to/templates/', 'ModuleName');
                }
                return $h;
            });
        }
    }
}
