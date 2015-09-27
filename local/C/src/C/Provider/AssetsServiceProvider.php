<?php
namespace C\Provider;

use C\Assets\BuiltinResponder;
use C\FS\Registry;
use C\FS\LocalFs;
use C\FS\KnownFs;
use C\Assets\Bridger;
use C\Misc\Utils;
use C\View\AssetsViewHelper;

use Silex\Application;
use Silex\ServiceProviderInterface;

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

        if (!isset($app['assets.www_path']))
            $app['assets.www_path'] = 'www/';
        if (!isset($app['assets.bridge_type']))
            $app['assets.bridge_type'] = 'builtin';
        if (!isset($app['assets.bridge_file_path']))
            $app['assets.bridge_file_path'] = '.assets_bridge';

        $app['assets.bridger'] = $app->share(function() use($app) {
            return new Bridger();
        });

        if (!isset($app['assets.cache_store_name']))
            $app['assets.cache_store_name'] = "assets-store";

        $app['assets.fs'] = $app->share(function() use($app) {
            $storeName = $app['assets.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            return new KnownFs(new Registry('assets-', $cache, [
                'basePath' => $app['project.path']
            ]));
        });
        $app['assets.responder'] = $app->share(function() use($app) {
            $responder = new BuiltinResponder();
//            $responder->setDocumentRoot($app['assets.www_path']);
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

        if (isset($app['layout.view'])) {
            $assetsViewHelper = new AssetsViewHelper();
            $assetsViewHelper->setPatterns($app["assets.patterns"]);
            $app['layout.view']->addHelper($assetsViewHelper);
        }

        if(!isset($app['assets.verbose'])) $app['assets.verbose'] = false;
        if (php_sapi_name()==='cli-server') {
            $app['assets.fs']->registry->loadFromCache();
            /* @var $responder \C\Assets\BuiltinResponder */
            $responder = $app['assets.responder'];
            $responder->respond($app['assets.verbose']);
        }

    }
}