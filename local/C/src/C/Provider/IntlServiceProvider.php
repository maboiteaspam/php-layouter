<?php
namespace C\Provider;

use C\FS\KnownFs;
use C\FS\LocalFs;
use C\FS\Registry;

use C\Intl\IntlInjector;
use C\Intl\IntlLoader;
use C\Intl\YmlIntlLoader;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use C\Watch\WatchedIntl;

class IntlServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        LocalFs::$record = $app['debug'];

        if (!isset($app['intl.cache_store_name']))
            $app['intl.cache_store_name'] = "intl-fs-store";

        $app['intl.fs'] = $app->share(function(Application $app) {
            $storeName = $app['intl.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            return new KnownFs(new Registry('intl-', $cache, [
                'basePath' => $app['project.path']
            ]));
        });

        if (!isset($app['intl-content.cache_store_name']))
            $app['intl-content.cache_store_name'] = "intl-content-store";

        $app['intl-content.cache'] = $app->share(function(Application $app) {
            $storeName = $app['intl-content.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            return $cache;
        });

        $app['intl.loader'] = $app->share(function(Application $app) {
            $loader = new IntlLoader();
            $loader->addLoader(
                new YmlIntlLoader($app['intl-content.cache'], 'yml'));
            return $loader;
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
        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->extend('watchers.watched', function($watched, Application $app) {
                $w = new WatchedIntl();
                $w->setRegistry($app['intl.fs']->registry);
                $w->setLoader($app['intl.loader']);
                $w->setName("intl");
                $watched[] = $w;
                return $watched;
            });
        }

        $app->before(function (Request $request) use ($app) {
            $app['intl.fs']->registry->loadFromCache();
            if (isset($app['translator'])) {
                $app['translator']->setLocale(
                    $request->getPreferredLanguage($app['layout.translator.available_languages'])
                );
            }
        });

        $app->before(function ($request, Application $app) {
            $injector = new IntlInjector();
            $injector->translator = $app['translator'];
            $injector->intlFS = $app['intl.fs'];
            $injector->loader = $app['intl.loader'];
            $app['layout']->beforeRender(function () use($injector, $app) {
                $injector->applyToLayout($app['layout']);
            }, Application::EARLY_EVENT);
        });

        if (isset($app['httpcache.tagger'])) {
            $fs = $app['intl.fs'];
            $tagger = $app['httpcache.tagger'];
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->tagDataWith('intl', function ($intl) use($fs) {
                $template = $fs->get($intl['item']);
                $h = '';
                if ($template) {
                    $h .= $template['sha1'].$template['dir'].$template['name'];
                } else if(LocalFs::file_exists($intl['item'])) {
                    $h .= LocalFs::file_get_contents($intl['item']);
                } else {
                    // that is bad, it means we have registered files
                    // that does not exists
                    // or that can t be located back.
                    //
                    // you may have forgotten somewhere
                    // $app['intl.fs']->register(__DIR__.'/path/to/templates/', 'ModuleName');
                }
                return $h;
            });
        }
    }
}
