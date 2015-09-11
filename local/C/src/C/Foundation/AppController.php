<?php
namespace C\Foundation;

use \Silex\Application;
use \Silex\Provider\HttpCacheServiceProvider;
use \Silex\Provider\UrlGeneratorServiceProvider;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\FormServiceProvider;

use C\Misc\Utils;
use C\LayoutBuilder\Layout\Layout;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

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
        $app = new Application($values);

        $app->register(new HttpCacheServiceProvider(), array(
            'http_cache.cache_dir' => __DIR__.'/cache/',
        ));
        $app->register(new UrlGeneratorServiceProvider());
        $app->register(new FormServiceProvider());

        $this->app = $app;

        return $app;
    }

    /**
     * @param array $options
     * @return Layout
     */
    public function getLayout ($options=[]) {
        $options = array_merge([
            'debug'         => $this->app['debug'],
            'dispatcher'    => $this->app['dispatcher'],
            'helpers'       => $this->getHelpers(),
            'imgUrls'       => [
                'blog_detail'   => '/images/blog/detail/:id.jpg',
                'blog_list'     => '/images/blog/list/:id.jpg',
            ],
        ], $options);
        $layout = new Layout($options);

        return $layout;
    }

    /**
     * @param array $options
     * @return Capsule
     */
    public function getDatabase ($options=[]) {
        $options = array_merge([
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ], $options);

        $capsule = new Capsule;
        $capsule->addConnection($options);
        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    }


    public function registerAssetsPaths ($projectPath, $type, $paths) {
        if (!is_dir("run/")) mkdir("run/");
        if ($type==='builtin') {
            $aliases = [];
            foreach ($paths as $i=>$path) {
                $aliases[substr(realpath($path), strlen(realpath($projectPath)))] = realpath($path);
            }
            file_put_contents('run/assets_path.php', "<?php return ".var_export($aliases, true).";");
        } else if ($type==='apache') {
            $aliases = "";
            foreach ($paths as $path) {
                $p = substr(realpath($path), strlen(realpath($projectPath))+1);
                $aliases = "Alias $p\t$path\n";
            }
            file_put_contents('run/assets_path.conf', $aliases);
        } else if ($type==='nginx') {
            $aliases = "";
            foreach ($paths as $path) {
                $p = substr(realpath($path), strlen(realpath($projectPath))+1);
                $aliases = "Alias $p\t$path\n";
            }
            file_put_contents('run/assets_path.conf', $aliases);
        }
    }

    public function getHelpers () {
        $app= $this->app;
        return [
            'urlFor'=> function ($name, $options=[], $only=[]) use($app) {
                $options = Utils::arrayPick($options, $only);
                return $app['url_generator']->generate($name, $options);
            },
            'urlArgs'=> function ($data=[], $only=[]) use($app) {
                if (isset($this->meta['from'])) {
                    $data = array_merge(Utils::arrayPick($this->meta, ['from']), $data);
                }
                $data = Utils::arrayPick($data, $only);
                $query = http_build_query($data);
                return $query ? '?'.$query : '';
            }
        ];
    }

    public static function respond(Application $app, Request $request) {
        $response = new Response();

        $response->setETag($app['layout']->getEtag());
        $response->mustRevalidate(true);
        $response->setPrivate(true);

        if ($response->isNotModified($request)) {
            return $response;
        }
        $response->setContent($app['layout']->render());
        return $response;
    }
}
