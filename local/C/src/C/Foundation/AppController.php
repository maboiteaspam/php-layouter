<?php
namespace C\Foundation;

use \Silex\Application;
use \Silex\Provider\HttpCacheServiceProvider;
use \Silex\Provider\UrlGeneratorServiceProvider;

use Symfony\Component\EventDispatcher\Tests\CallableClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;

use C\Misc\Utils;
use C\LayoutBuilder\Layout\Layout;
use C\FS\KnownFs;
use C\FS\Registry;
use C\FS\LocalFs;
use C\Schema\Loader as SchemaLoader;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

use Igorw\Silex\ConfigServiceProvider;

class AppController{

    /**
     * @var Application
     */
    public $app;

    /**
     * @param array $values
     * @return Application
     */
    public function getApp (array $values = array()) {

        $env = getenv('APP_ENV') ? getenv('APP_ENV') : 'dev';
        $projectPath = $values['projectPath'];

        $app = new Application();
        $this->app =  $app;
        $values = array_merge([
            'env'=>$env,
            'monolog.logfile' => __DIR__.'/run/development.log',
            'security.firewalls' => [],
        ], $values);
        $app->register(new ConfigServiceProvider("$projectPath/config.php", [
            'projectPath' => $projectPath,
        ]));
        foreach( $values as $key=>$value ){
            $app[$key] = $value;
        }
        //$app->register(new MonologServiceProvider([
        //]));
        //$app->register(new SessionServiceProvider( ));
        //$app->register(new SecurityServiceProvider([
        //]));
        //$app->register(new RememberMeServiceProvider([
        //]));
        $app->register(new TranslationServiceProvider( ));
        //$app->register(new HttpCacheServiceProvider(), array(
        //    'http_cache.cache_dir' => $app['public_build_dir']."/http_cache",
        //));
        $app->register(new UrlGeneratorServiceProvider());
        $app->register(new FormServiceProvider());


        LocalFs::$record = $app['debug'];

        $build_dir = $app['private_build_dir'];
        if ($this->isEnv('dev') && !LocalFs::is_dir($build_dir)) LocalFs::mkdir($build_dir);

        $app['dispatcher']->addListener('c_modules_loaded', function () use(&$app) {
            $env = $app['env'];

            $settings = $app["capsule.settings.$env"];

            $capsule = new Capsule;
            $capsule->setEventDispatcher(new Dispatcher(new Container));
            $capsule->addConnection($settings);
            $capsule->bootEloquent();
            $capsule->setAsGlobal();

            $app['capsule'] = $capsule;
            $app['schema_loader']->bootDb($settings, $env);
        });


        $app['assetsFS'] = new KnownFs(new Registry($app['private_build_dir']."/assets.php"));
        $app['assetsFS']->setBasePath($projectPath);
        $app['templatesFS'] = new KnownFs(new Registry($app['private_build_dir']."/templates.php"));
        $app['templatesFS']->setBasePath($projectPath);
        $app['schemasFS'] = new Registry($app['private_build_dir']."/schemas.php");
        $app['schemasFS']->setBasePath($projectPath);

        $app['schema_loader'] = new SchemaLoader($app['schemasFS']);

        $app['layout'] = new Layout([
            'debug'         => $app['debug'],
            'dispatcher'    => $app['dispatcher'],
            'helpers'       => $this->getHelpers(),
            'imgUrls'       => [],
        ]);

        $app['layout_responder'] = $app->protect(function () use (&$app, $build_dir) {
            $request = $app['request'];
            /* @var $request \Symfony\Component\HttpFoundation\Request */
            $response = new Response();


            $TaggedResource = $app['layout']->getTaggedResource();
            $conn = $app['capsule']->getConnection();
            $templatesFS = $app['templatesFS'];
            $assetsFS = $app['assetsFS'];
            $sqlRun = function ($sql) use($conn) {
                return $conn->select($sql);
            };
            $fsSign = function ($file) use($templatesFS, $assetsFS) {
                $template = $templatesFS->get($file);
                if ($template) {
                    return $template['sha1'].$template['dir'].$template['name'];
                } else  {
                    $asset = $assetsFS->get($file);
                    if ($asset) {
                        return $template['sha1'].$template['dir'].$template['name'];
                    }
                }
                return file_exists($file)?file_get_contents($file):'';
            };
            $etag = $TaggedResource->sign($sqlRun, $fsSign);

            // this is super important to get etag working properly.
            $response->setProtocolVersion('1.1');
            $response->mustRevalidate(true);
            $response->setPrivate(true);
            $response->setETag($etag);

            if ($response->isNotModified($request)) {
                return $response;
            }

            $app['layout']->emit('before_layout_render');
            $app['layout']->render();
            $app['layout']->emit('after_layout_render');
            $body = $app['layout']->getRoot()->body;
            $response->setContent( $body );

            if ($etag) {
                file_put_contents("$build_dir/etag-$etag.php",
                    "<?php return ".var_export(serialize($TaggedResource), true).";");
                file_put_contents("$build_dir/body-$etag.php",
                    "<?php return ".var_export($body, true).";");
            }

            return $response;
        });
        $app->before(function (Request $request, Application $app) use($build_dir) {
            if ($request->isMethodSafe()) {
                $conn = $app['capsule']->getConnection();
                $sqlRun = function ($sql) use($conn) {
                    return $conn->select($sql);
                };
                $fsSign = function ($file) {
                    return file_exists($file)?file_get_contents($file):'';
                };
                $etags = $request->getETags();
                foreach($etags as $etag){
                    $f = "$build_dir/etag-$etag.php";
                    if (file_exists($f)) {
                        $TaggedResource = unserialize(include($f));
                        if ($TaggedResource->isFresh($sqlRun, $fsSign)) {
                            $response = new Response();
                            $response->setProtocolVersion('1.1');
                            $response->mustRevalidate(true);
                            $response->setPrivate(true);
                            $response->setETag($etag);
                            if ($response->isNotModified($request)) {
                                $response->setContent( "CACHED".unserialize(include($f)) );
                                return $response;
                            }
                        }
                    }
                }
            }
        });

        $that = $this;

        $serverType = $app['server_type'];
        if ($that->isEnv('dev')) {
            $app['schemasFS']->loadFromFile();
        }
        $app['templatesFS']->registry->loadFromFile();
        $app['assetsFS']->registry->loadFromFile();

        if ($that->isEnv('dev')) {
            $app['dispatcher']->addListener('c_modules_loaded', function () use(&$app) {
                $app['schemasFS']->saveToFile();
                $app['templatesFS']->registry->saveToFile();
                $app['assetsFS']->registry->saveToFile();
            });
            $app->after(function () use(&$app, &$that, $build_dir, $serverType) {
                $that->bridgeAssetsPath("$build_dir/assets_path_{$serverType}_bridge.php",
                    $serverType, $app['assetsFS']);

//    $that->bridgeAssetsPath("$build_dir/assets_path_apache_bridge.conf", 'apache',
// $context['assetsFS']);
//    $that->bridgeAssetsPath("$build_dir/assets_path_nginx_bridge.conf", 'nginx',
// $context['assetsFS']);

//            var_dump($app['assetsFS']->fs->calls);
//            var_dump($app['templatesFS']->fs->calls);

            });
        }

        return $app;
    }


    public function isEnv($some) {
        return $some==$this->app['env'];
    }

    public function bridgeAssetsPath ($file, $type, KnownFs $fs) {
        $projectPath = $fs->registry->config['basePath'];
        $paths = array_unique($fs->registry->config['paths']);
        $aliases = [];
        if ($type==='builtin') {
            foreach ($paths as $i=>$path) {
                $urlAlias = substr(realpath($path), strlen(realpath($projectPath)));
                $urlAlias = str_replace(DIRECTORY_SEPARATOR, "/", $urlAlias);
                $aliases[$urlAlias] = realpath($path);
            }
            $aliases = "<?php return ".var_export($aliases, true).";\n";
        } else if ($type==='apache') {
            $aliases = "";
            foreach ($paths as $path) {
                $urlAlias = substr(realpath($path), strlen(realpath($projectPath))+1);
                $urlAlias = str_replace(DIRECTORY_SEPARATOR, "/", $urlAlias);
                $aliases .= "Alias $urlAlias\t$path\n";
            }
        } else if ($type==='nginx') {
            $aliases = "";
            foreach ($paths as $path) {
                $urlAlias = substr(realpath($path), strlen(realpath($projectPath))+1);
                $urlAlias = str_replace(DIRECTORY_SEPARATOR, "/", $urlAlias);
                $aliases .= "Alias $urlAlias\t$path\n";
            }
        }
        return LocalFs::file_put_contents($file, $aliases);
    }

    public function getHelpers () {
        $app = $this->app;
        return [
            'urlFor'=> function ($name, $options=[], $only=[]) use(&$app) {
                $options = Utils::arrayPick($options, $only);
                return $app['url_generator']->generate($name, $options);
            },
            'urlArgs'=> function ($data=[], $only=[]) use(&$app) {
                if (isset($this->meta['from'])) {
                    $data = array_merge(Utils::arrayPick($this->meta, ['from']), $data);
                }
                $data = Utils::arrayPick($data, $only);
                $query = http_build_query($data);
                return $query ? '?'.$query : '';
            }
        ];
    }
}
