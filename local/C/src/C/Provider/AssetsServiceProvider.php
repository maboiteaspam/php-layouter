<?php
namespace C\Provider;

use C\Assets\BuiltinResponder;
use C\FS\LocalFs;
use C\FS\KnownFs;
use C\Assets\Bridger;
use C\FS\Registry;

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

        if (!isset($app['assets.fs_file_path']))
            $app['assets.fs_file_path'] = '.assets_fs_cache';

        $app['assets.fs'] = $app->share(function() use($app) {
            return new KnownFs(new Registry($app['assets.fs_file_path'], [
                'basePath' => $app['projectPath']
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
        $app['assets.fs']->registry->loadFromFile();

        if (isset($app['httpcache.tagger'])) {
            $fs = $app['assets.fs'];
            $tagger = $app['httpcache.tagger'];
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->tagDataWith('file', function ($file) use($fs) {
                $template = $fs->get($file);
                $h = '';
                if ($template) {
                    $h .= $template['sha1'].$template['dir'].$template['name'];
                } else if(LocalFs::file_exists($file)) {
                    $h .= LocalFs::file_get_contents($file);
                }
                return $h;
            });
        }

        if (php_sapi_name()==='cli-server') {
            $app['dispatcher']->addListener("boot_done", function () use($app) {
                /* @var $responder \C\Assets\BuiltinResponder */
                $responder = $app['assets.responder'];
                $responder->respond();
            });
        }
        $app["dispatcher"]->addListener('init.app', function() use($app) {
            $app['assets.fs']->registry->saveToFile();
            $app['assets.bridger']->generate(
                $app['assets.bridge_file_path'],
                $app['assets.bridge_type'],
                $app['assets.fs']);
        });
        $app["dispatcher"]->addListener('dump.fs_file_path', function() use($app) {
            echo $app['assets.fs']->registry->file."\n";
        });
        $app["dispatcher"]->addListener('dump.fs', function() use($app) {
            $app['assets.fs']->registry->saveToFile();
            $app['assets.bridger']->generate(
                $app['assets.bridge_file_path'],
                $app['assets.bridge_type'],
                $app['assets.fs']);
        });
    }
}