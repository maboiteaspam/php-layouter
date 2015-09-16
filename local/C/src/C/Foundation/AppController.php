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

        $app = new Application();
        $this->app =  $app;

        $app->register(new ConfigServiceProvider("$projectPath/config.php", [
            'projectPath' => $projectPath,
        ]));
        $values = array_merge(['env'=>$env], $values);
        foreach( $values as $key=>$value ){
            $app[$key] = $value;
        }

        $app->register(new HttpCacheServiceProvider(), array(
            'http_cache.cache_dir' => $app['public_build_dir']."/http_cache",
        ));
        $app->register(new UrlGeneratorServiceProvider());
        $app->register(new FormServiceProvider());

        $build_dir = $app['private_build_dir'];

        if ($this->isEnv('dev') && !is_dir($build_dir)) mkdir($build_dir);

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
            $app['layout']->render();
            $app['layout']->emit('after_layout_render');
            $response->setContent(
                $app['layout']->getRoot()->body
            );
            return $response;
        });

        $that = $this;

        $serverType = $app['server_type'];
        if ($that->isEnv('dev')) {
            $app['schemasFS']->loadFromFile();
        }
        $app['templatesFS']->registry->loadFromFile();
        $app['assetsFS']->registry->loadFromFile();

        if ($that->isEnv('dev')) {
            $app->after(function () use(&$app) {
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
