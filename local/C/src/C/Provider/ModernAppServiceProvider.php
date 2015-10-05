<?php
namespace C\Provider;

use C\FS\KnownFs;
use C\FS\LocalFs;
use C\FS\Registry;
use C\Watch\WatchedModernLayout;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class ModernAppServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        if (!isset($app['modern.fs_store_name']))
            $app['modern.fs_store_name'] = "modern-layout-store";

        $app['modern.fs'] = $app->share(function(Application $app) {
            $storeName = $app['modern.fs_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            return new KnownFs(new Registry('modern-layout-', $cache, [
                'basePath' => $app['project.path']
            ]));
        });

        if (!isset($app['modern.layout_store_name']))
            $app['modern.layout_store_name'] = "modern-layout-store";

        $app['modern.layout.store'] = $app->share(function (Application $app) {
            $store = new \C\ModernApp\File\Store();

            $store->setModernLayoutFS($app['modern.fs']);

            $storeName = $app['modern.layout_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            $store->setCache($cache);

            return $store;
        });
        $app['modern.layout.helpers'] = $app->share(function (Application $app) {
            // @todo this should probably be moved away into separate service providers, for now on it s only inlined
            $helpers = [];
            $helpers[] = new \C\ModernApp\File\Helpers\LayoutHelper();
            $helpers[] = new \C\ModernApp\File\Helpers\AssetsHelper();
            $helpers[] = new \C\ModernApp\File\Helpers\jQueryHelper();
            $helpers[] = new \C\ModernApp\File\Helpers\IntlHelper();
            $helpers[] = new \C\ModernApp\File\Helpers\DashboardHelper();
            $helpers[] = new \C\ModernApp\File\Helpers\RequestHelper();
            return $helpers;
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
        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(__DIR__.'/../ModernApp/Dashboard/assets/', 'Dashboard');
            $app['assets.fs']->register(__DIR__.'/../ModernApp/jQuery/assets/', 'jQuery');
        }
        if (isset($app['layout.fs'])) {
            $app['layout.fs']->register(__DIR__.'/../ModernApp/HTML/templates/', 'HTML');
            $app['layout.fs']->register(__DIR__.'/../ModernApp/Dashboard/templates/', 'Dashboard');
            $app['layout.fs']->register(__DIR__.'/../ModernApp/jQuery/templates/', 'jQuery');
        }
        if (isset($app['modern.fs'])) {
            $app['modern.fs']->register(__DIR__.'/../ModernApp/HTML/layouts/', 'HTML');
        }

        if (isset($app['httpcache.tagger'])) {
            $fs = $app['modern.fs'];
            $tagger = $app['httpcache.tagger'];
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->tagDataWith('modern.layout', function ($file) use($fs) {
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
                    // $app['modern.fs']->register(__DIR__.'/path/to/templates/', 'ModuleName');
                }
                return $h;
            });
        }

        $app->before(function(Request $request, Application $app){
            if ($request->isXmlHttpRequest()) {
                $app['layout']->requestMatcher->setRequestKind('ajax');
            }
            $app['modern.fs']->registry->loadFromCache();
        }, Application::EARLY_EVENT);

        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->extend('watchers.watched', function($watched, Application $app) {
                $w = new WatchedModernLayout();
                $w->setStore($app['modern.layout.store']);
                $w->setRegistry($app['modern.fs']->registry);
                $w->setName("modern.fs");
                $watched[] = $w;
                return $watched;
            });
        }

    }
}