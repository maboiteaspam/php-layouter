<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use C\Schema\Loader;
use C\FS\Registry;
use C\FS\LocalFs;

class CapsuleServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {

        $app['capsule.connection_defaults'] = array(
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => null,
            'username' => 'root',
            'password' => null,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => null,
            'logging' => false,
        );

        $app['capsule.global'] = true;
        $app['capsule.eloquent'] = true;
        $app['capsule.container'] = $app->share(function() {
            return new Container;
        });

        if (!isset($app['capsule.cache_store_name']))
            $app['capsule.cache_store_name'] = "capsule-store";

        $app['capsule.schema'] = $app->share(function(Application $app) {
            $storeName = $app['capsule.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            $loader = new Loader(new Registry('capsule-', $cache, [
                'basePath' => $app['project.path']
            ]));
            $loader->setCapsule($app['capsule']);
            return $loader;
        });

        $app['capsule.dispatcher'] = $app->share(function(Application $app) {
            return new Dispatcher($app['capsule.container']);
        });

//        if (class_exists('Illuminate\Cache\CacheManager')) {
//            $app['capsule.cache_manager'] = $app->share(function() use($app) {
//                return new CacheManager($app['capsule.container']);
//            });
//        }

        $app['capsule'] = $app->share(function(Application $app) {

            $capsule = new Capsule($app['capsule.container']);
            $capsule->setEventDispatcher($app['capsule.dispatcher']);
            if (isset($app['capsule.cache_manager']) && isset($app['capsule.cache'])) {
//                $capsule->setCacheManager($app['capsule.cache_manager']);
                foreach ($app['capsule.cache'] as $key => $value) {
                    $app['capsule.container']->offsetGet('config')->offsetSet('cache.' . $key, $value);
                }
            }
            if ($app['capsule.global']) {
                $capsule->setAsGlobal();
            }
            if ($app['capsule.eloquent']) {
                $capsule->bootEloquent();
            }
            if (! isset($app['capsule.connections'])) {
                $app['capsule.connections'] = array(
                    'default' => (isset($app['capsule.connection']) ? $app['capsule.connection'] : array()),
                );
            }

            foreach ($app['capsule.connections'] as $connection => $options) {
                $options = array_replace($app['capsule.connection_defaults'], $options);
                $capsule->addConnection($options, $connection);
            }

            if (!isset($app['capsule.use_connection'])) {
                $app['capsule.use_connection'] = $app['env'];
            }
            if (!isset($app['capsule.connections']['default'])) {
                $capsule->addConnection($app['capsule.connections'][$app['capsule.use_connection']], 'default');
            }

            return $capsule;

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
        if (isset($app['capsule.eloquent'])) {
            if (isset($app['httpcache.tagger'])) {
                $tagger = $app['httpcache.tagger'];
                $capsule = $app['capsule'];
                /* @var $tagger \C\TagableResource\ResourceTagger */
                $tagger->tagDataWith('sql', function ($sql) use($capsule) {
                    return $capsule->getConnection()->select($sql);
                });
            }
        }
        $app->before(function () use($app) {
            $connections = $app['capsule.connections'];
            $capsule = $app['capsule'];
            foreach ($connections as $connection => $options) {
                $options = array_replace($app['capsule.connection_defaults'], $options);
                if ($options['logging']) {
                    $capsule->connection($connection)->enableQueryLog();
                }
            }
        });
    }

}