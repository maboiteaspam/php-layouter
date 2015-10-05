<?php
namespace C\Provider;

use C\Assets\AssetsInjector;
use C\Assets\BuiltinResponder;
use C\FS\Registry;
use C\FS\LocalFs;
use C\FS\KnownFs;
use C\Assets\Bridger;
use C\View\Helper\AssetsViewHelper;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use C\Watch\WatchedRegistry;

class AssetsServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        LocalFs::$record = $app['debug'];

        // relative path to www
        if (!isset($app['assets.www_path']))
            $app['assets.www_path'] = 'www/';

        // it should match the web server
        // used by your app (http nginx biultin)
        if (!isset($app['assets.bridge_type']))
            $app['assets.bridge_type'] = 'builtin';

        // the path used to record bridge content
        if (!isset($app['assets.bridge_file_path']))
            $app['assets.bridge_file_path'] = '.assets_bridge';

        // bridger will help to bridge assets enclosed into modules
        // to www web server
        $app['assets.bridger'] = $app->share(function() {
            return new Bridger();
        });

        if (!isset($app['assets.cache_store_name']))
            $app['assets.cache_store_name'] = "assets-store";

        $app['assets.fs'] = $app->share(function(Application $app) {
            $storeName = $app['assets.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            return new KnownFs(new Registry('assets-', $cache, [
                'basePath' => $app['project.path']
            ]));
        });
        $app['assets.responder'] = $app->share(function(Application $app) {
            $responder = new BuiltinResponder();
            $responder->wwwDir = $app['documentRoot'];
            $responder->setFS($app['assets.fs']);
            return $responder;
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

        if (isset($app['httpcache.tagger'])) {
            $fs = $app['assets.fs'];
            $tagger = $app['httpcache.tagger'];
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->tagDataWith('asset', function ($file) use($fs) {
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
                }
                return $h;
            });
        }

        if (isset($app['layout'])) {
            $app->before(function (Request $request, Application $app) {
                $injector = new AssetsInjector();
                $injector->concatenate = $app['assets.concat'];
                $injector->assetsFS = $app['assets.fs'];
                $injector->wwwDir = $app['assets.www_dir'];
                $injector->buildDir = $app['assets.build_dir'];
                $layout = $app['layout'];
                /* @var $layout \C\Layout\Layout */
                $layout->beforeRender(function () use($injector, $app) {
                    $injector->applyFileAssets($app['layout']);
                });
                $layout->afterResolve(function () use($injector, $app) {
                    $injector->applyInlineAssets($app['layout']);
                });
                if ($injector->concatenate) {
                    $app->after(function() use($injector, $app){
                        $injector->createMergedAssetsFiles($app['layout']);
                    }, Application::LATE_EVENT);
                }
            });
        }

        if (isset($app['layout.view'])) {
            $assetsViewHelper = new AssetsViewHelper();
            $assetsViewHelper->setPatterns($app["assets.patterns"]);
            $app['layout.view']->addHelper($assetsViewHelper);
        }

        if(!isset($app['assets.verbose'])) $app['assets.verbose'] = false;
        if (php_sapi_name()==='cli-server') {
//            $app['assets.fs']->registry->loadFromCache();
            /* @var $responder \C\Assets\BuiltinResponder */
            $responder = $app['assets.responder'];
            $app['assets.fs']->registry->loadFromCache();
            $responder->respond($app['assets.verbose']);
        }

        $app->before(function($request, Application $app){
            $app['assets.fs']->registry->loadFromCache();
        }, Application::EARLY_EVENT);

        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->extend('watchers.watched', function($watched, Application $app) {
                $w = new WatchedRegistry();
                $w->setRegistry($app['assets.fs']->registry);
                $w->setName("assets.fs");
                $watched[] = $w;
                return $watched;
            });
        }

    }
}