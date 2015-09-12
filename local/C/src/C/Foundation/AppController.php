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

class AppController{

    public $appCtx = [];

    /**
     * @param array $values
     * @return Application
     */
    public function getApp (array $values = array()) {

        $projectPath = $values['projectPath'];

        $app = new Application($values);
        $this->appCtx =  new \ArrayObject(
            array_merge($this->appCtx, $values, ['app'=>$app])
        );
        $context = $this->appCtx;

        $app->register(new HttpCacheServiceProvider(), array(
            'http_cache.cache_dir' => $values['public_build_dir']."/http_cache",
        ));
        $app->register(new UrlGeneratorServiceProvider());
        $app->register(new FormServiceProvider());

        $context['capsule'] = new Capsule;
        $context['capsule']->setEventDispatcher(new Dispatcher(new Container));

        $context['assetsFS'] = new KnownFs();
        $context['assetsFS']->setBasePath($projectPath);
        $context['schemasFS'] = new Registry();
        $context['schemasFS']->setBasePath($projectPath);

        $context['schema_loader'] = new SchemaLoader($context['schemasFS']);

        $context['layout'] = new Layout([
            'debug'         => $context['debug'],
            'dispatcher'    => $app['dispatcher'],
            'helpers'       => $this->getHelpers(),
            'imgUrls'       => [],
        ]);

        $context['layout_responder'] = function () use (&$app, &$context) {
            $request = $app['request'];
            /* @var $request \Symfony\Component\HttpFoundation\Request */
            $response = new Response();

            $response->setETag($context['layout']->getEtag());
            $response->mustRevalidate(true);
            $response->setPrivate(true);

            if ($response->isNotModified($request)) {
                return $response;
            }
            $response->setContent($context['layout']->render());
            return $response;
        };


        if (!is_dir("$projectPath/run/")) mkdir("$projectPath/run/");
        $private_build_dir = $values['private_build_dir'];

        $serverType = $values['server_type'];
        $context['schemasFS']->loadFromFile($values['private_build_dir']."/schemas.php");
        $context['assetsFS']->registry->loadFromFile($values['private_build_dir']."/assets.php");

        $that = $this;
        $app->finish(function () use(&$context, &$that, $private_build_dir, $serverType) {
            if ($that->isEnv('dev')) {
                $context['schemasFS']->saveToFile("$private_build_dir/schemas.php");
                $context['assetsFS']->registry->saveToFile("$private_build_dir/assets.php");
                $that->bridgeAssetsPath("$private_build_dir/assets_path_{$serverType}_bridge.php",
                    $serverType, $context['assetsFS']);
//    $that->bridgeAssetsPath("$projectPath/run/assets_path_apache_bridge.conf", 'apache',
// $context['assetsFS']);
//    $that->bridgeAssetsPath("$projectPath/run/assets_path_nginx_bridge.conf", 'nginx',
// $context['assetsFS']);
//                var_dump($context['schemasFS']->calls);
            }
        });
        return $app;
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
        $app = $this->appCtx['app'];
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

    public function isEnv($some) {
        return true;
    }
}
