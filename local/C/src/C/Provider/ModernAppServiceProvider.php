<?php
namespace C\Provider;

use C\FS\KnownFs;
use C\FS\LocalFs;
use C\FS\Registry;
use Silex\Application;
use Silex\ServiceProviderInterface;

class ModernAppServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        if (!isset($app['modern.fs_store_name']))
            $app['modern.fs_store_name'] = "modern-layout-store";

        $app['modern.fs'] = $app->share(function(Application $app) {
            $storeName = $app['modern.fs_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            return new KnownFs(new Registry('modern-layout-', $cache, [
                'basePath' => $app['project.path']
            ]));
        });

        if (!isset($app['modern.layout_store_name']))
            $app['modern.layout_store_name'] = "modern-layout-store";

        $app['modern.layout'] = $app->share(function (Application $app) {
            $transform = new \C\ModernApp\File\Transforms();
            $transform->setLayout($app['layout']);
            $transform->setModernLayoutFS($app['modern.fs']);

            $helpers = $app['modern.layout.helpers'];
            foreach($helpers as $helper){
                $transform->addHelper($helper);
            }

            $storeName = $app['modern.layout_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            $transform->setCache($cache);

            return $transform;
        });
        $app['modern.layout.helpers'] = $app->share(function (Application $app) {
            // @todo this should probably be moved away into separate service providers, for now on it s only inlined
            $helpers = [];
            $helpers[] = new \C\ModernApp\File\Helpers\LayoutHelper();
            $helpers[] = new \C\ModernApp\File\Helpers\AssetsHelper();
            return $helpers;
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
        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(__DIR__.'/../ModernApp/Dashboard/assets/', 'Dashboard');
            $app['assets.fs']->register(__DIR__.'/../ModernApp/jQuery/assets/', 'jQuery');
        }
        if (isset($app['layout.fs'])) {
            $app['layout.fs']->register(__DIR__.'/../ModernApp/HTML/templates/', 'HTML');
            $app['layout.fs']->register(__DIR__.'/../ModernApp/Dashboard/templates/', 'Dashboard');
            $app['layout.fs']->register(__DIR__.'/../ModernApp/jQuery/templates/', 'jQuery');
        }

        if (isset($app['httpcache.tagger'])) {
            $fs = $app['modern.fs'];
            $tagger = $app['httpcache.tagger'];
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->tagDataWith('modern.layout', function ($file) use($fs) {
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
    }
}