<?php
namespace C\Foundation;

use \Silex\Application;
use \Silex\Provider\HttpCacheServiceProvider;
use \Silex\Provider\UrlGeneratorServiceProvider;

use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\FormServiceProvider;

use C\Misc\Utils;
use C\LayoutBuilder\Layout\Layout;
use C\FS\KnownFs;
use C\FS\Registry;
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

        $app = new Application(array_merge(['env'=>$env], $values));
        $this->app =  $app;

        $app->register(new ConfigServiceProvider("$projectPath/config.php", [
            'projectPath' => $projectPath,
        ]));

        $app->register(new HttpCacheServiceProvider(), array(
            'http_cache.cache_dir' => $app['public_build_dir']."/http_cache",
        ));
        $app->register(new UrlGeneratorServiceProvider());
        $app->register(new FormServiceProvider());

        $app['capsule'] = new Capsule;
        $app['capsule']->setEventDispatcher(new Dispatcher(new Container));

        $build_dir = $app['private_build_dir'];
        if ($this->isEnv('dev') && !is_dir($build_dir)) mkdir($build_dir);
        $app['dispatcher']->addListener('c_modules_loaded', function () use(&$app) {
            $env = $app['env'];

            $exists = false;
            $settings = $app["capsule.settings.$env"];
            $connection = $app["capsule.connection.$env"];

            /* @var $connection callable */

            if ($env==='dev') {
                $exists = file_exists($settings['database']);
                if ($exists) {
                    if (!$app['schema_loader']->isFresh()) {
                        unlink($settings['database']);
                        $exists = false;
                    }
                }
            }


            if ($settings["driver"]==='sqlite') {
                if ($settings["database"]!=='memory') {
                    touch($settings["database"]);
                }
            }
            $connection();
            $app['capsule']->bootEloquent();
            $app['capsule']->setAsGlobal();

            if ($env==='dev') {
                if (!$exists) {
                    $app['schema_loader']->build();
                    $app['schema_loader']->populate();
                }
            }
        });


        $app['assetsFS'] = new KnownFs();
        $app['assetsFS']->setBasePath($projectPath);
        $app['templatesFS'] = new KnownFs();
        $app['templatesFS']->setBasePath($projectPath);
        $app['schemasFS'] = new Registry();
        $app['schemasFS']->setBasePath($projectPath);

        $app['schema_loader'] = new SchemaLoader($app['schemasFS']);

        $app['layout'] = new Layout([
            'debug'         => $app['debug'],
            'dispatcher'    => $app['dispatcher'],
            'helpers'       => $this->getHelpers(),
            'imgUrls'       => [],
        ]);

        $app['layout_responder'] = $app->protect(function () use (&$app) {
            $request = $app['request'];
            /* @var $request \Symfony\Component\HttpFoundation\Request */
            $response = new Response();

            $response->setETag($app['layout']->getEtag());
            $response->mustRevalidate(true);
            $response->setPrivate(true);

            if ($response->isNotModified($request)) {
                return $response;
            }
            $app['layout']->emit('before_layout_render');
            $response->setContent($app['layout']->render());
            $app['layout']->emit('after_layout_render');
            return $response;
        });



        $serverType = $app['server_type'];
        $app['schemasFS']->loadFromFile($app['private_build_dir']."/schemas.php");
        $app['templatesFS']->registry->loadFromFile($app['private_build_dir']."/templates.php");
        $app['assetsFS']->registry->loadFromFile($app['private_build_dir']."/assets.php");

        $that = $this;
        if ($that->isEnv('dev')) {
            $app->after(function () use(&$app, &$that, $build_dir, $serverType) {
                $app['schemasFS']->saveToFile("$build_dir/schemas.php");
                $app['templatesFS']->registry->saveToFile("$build_dir/templates.php");
                $app['assetsFS']->registry->saveToFile("$build_dir/assets.php");
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
                $aliases[substr(realpath($path), strlen(realpath($projectPath)))] = realpath($path);
            }
            $aliases = "<?php return ".var_export($aliases, true).";\n";
        } else if ($type==='apache') {
            $aliases = "";
            foreach ($paths as $path) {
                $p = substr(realpath($path), strlen(realpath($projectPath))+1);
                $aliases .= "Alias $p\t$path\n";
            }
        } else if ($type==='nginx') {
            $aliases = "";
            foreach ($paths as $path) {
                $p = substr(realpath($path), strlen(realpath($projectPath))+1);
                $aliases .= "Alias $p\t$path\n";
            }
        }
        return file_put_contents($file, $aliases);
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
