<?php

namespace C\BlogData;

use Silex\Application;
use Silex\ServiceProviderInterface;
use \C\BlogData\PO as PO;
use \C\BlogData\Eloquent as Eloquent;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app
     **/
    public function register(Application $app)
    {
        if (!isset($app['blogdata.provider'])) $app['blogdata.provider'] = 'PO';

        $app['blogdata.entry'] = $app->share(function () use($app) {
            $repo = null;
            if ($app['blogdata.provider']==='PO') {
                $repo = new PO\EntryRepository();
            } else if ($app['blogdata.provider']==='Eloquent') {
                $repo = new Eloquent\EntryRepository();
                $repo->setCapsule($app['capsule']);
            }
            $repo->setRepositoryName('blogdata.entry');
            return $repo;
        });

        $app['blogdata.comment'] = $app->share(function () use($app) {
            $repo = null;
            if ($app['blogdata.provider']==='PO') {
                $repo = new PO\CommentRepository();
            } else if ($app['blogdata.provider']==='Eloquent') {
                $repo = new Eloquent\CommentRepository();
                $repo->setCapsule($app['capsule']);
            }
            $repo->setRepositoryName('blogdata.comment');
            return $repo;
        });

        $app['blogdata.schema'] = $app->share(function () use($app) {
            $schema = null;
            if ($app['blogdata.provider']==='PO') {
                $schema = new PO\Schema;
            } else if ($app['blogdata.provider']==='Eloquent') {
                $schema = new Eloquent\Schema($app['capsule']);
            }
            return $schema;
        });
    }
    /**
     * @param Application $app Silex application instance.
     *
     * @return void
     **/
    public function boot(Application $app)
    {
        if (isset($app['capsule.schema'])) {
            $app['capsule.schema']->register($app['blogdata.schema']);
        }
    }
}