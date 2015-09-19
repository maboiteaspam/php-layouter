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

        if (!isset($app['capsule.schema_file_cache']))
            $app['capsule.schema_file_cache'] = '.capsule_schema_cache';

        $app['capsule.schema'] = $app->share(function() use($app) {
            $loader = new Loader(new Registry($app['capsule.schema_file_cache'], [
                'basePath' => $app['projectPath']
            ]));
            $loader->setCapsule($app['capsule']);
            return $loader;
        });

        $app['capsule.dispatcher'] = $app->share(function() use($app) {
            return new Dispatcher($app['capsule.container']);
        });

//        if (class_exists('Illuminate\Cache\CacheManager')) {
//            $app['capsule.cache_manager'] = $app->share(function() use($app) {
//                return new CacheManager($app['capsule.container']);
//            });
//        }

        $app['capsule'] = $app->share(function() use ($app) {

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
                $logging = $options['logging'];
                unset($options['logging']);

                $capsule->addConnection($options, $connection);


                if ($logging) {
                    $capsule->connection($connection)->enableQueryLog();
                } else {
                    $capsule->connection($connection)->disableQueryLog();
                }
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
        if ($app['capsule.eloquent']) {

            $this->sqliteSetup($app['capsule.connections']);

            $capsule = $app['capsule'];
            if (isset($app['httpcache.tagger'])) {
                $tagger = $app['httpcache.tagger'];
                /* @var $tagger \C\TagableResource\ResourceTagger */
                $tagger->tagDataWith('sql', function ($sql) use($capsule) {
                    return $capsule->getConnection()->select($sql);
                });
            }


            $app["dispatcher"]->addListener('boot_done', function() use($app) {
            });
            $app["dispatcher"]->addListener('init.app', function() use($app) {
                $app['capsule.schema']->loadSchemas();
                $app['capsule.schema']->cleanDb();
                $app['capsule.schema']->initDb();
                $app['capsule.schema']->registry->saveToFile();
            });
            $app["dispatcher"]->addListener('init.schema', function() use($app) {
                $app['capsule.schema']->loadSchemas();
                $app['capsule.schema']->cleanDb();
                $app['capsule.schema']->initDb();
            });
            $app["dispatcher"]->addListener('refresh.schema', function() use($app) {
                $app['capsule.schema']->loadSchemas();
                $app['capsule.schema']->refreshDb();
            });
            $app["dispatcher"]->addListener('dump.fs_file_path', function() use($app) {
                echo $app['capsule.schema']->registry->file."\n";
            });
            $app["dispatcher"]->addListener('dump.fs', function() use($app) {
                $app['capsule.schema']->registry->saveToFile();
            });
        }
    }

    public function sqliteSetup($connections){
        foreach ($connections as $connection => $options) {
            if ($options["driver"]==='sqlite') {
                if ($options["database"]!==':memory:') {
                    $exists = LocalFs::file_exists($options['database']);
                    if (!$exists) {
                        $dir = dirname($options["database"]);
                        if (!LocalFs::is_dir($dir)) LocalFs::mkdir($dir, 0700, true);
                        LocalFs::touch($options["database"]);
                    }
                }
            }
        }
    }
}