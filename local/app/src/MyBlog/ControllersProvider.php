<?php
namespace MyBlog;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ControllerProviderInterface;

class ControllersProvider implements
    ServiceProviderInterface,
    ControllerProviderInterface
{
    /**
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        $app['myblog.controllers'] = $app->share(function() use ($app) {
            $controllers = new Controllers($app['blogdata.entry'], $app['blogdata.comment']);
            $controllers->setBlogTransforms($app['myblog.transforms']);
            $controllers->setjQueryTransforms($app['layout.jquery.transforms']);
            $controllers->setStaticTransforms($app['layout.static.transforms']);
            return $controllers;
        });
        $app['myblog.transforms'] = $app->share(function() use ($app) {
            $T = new Transforms($app['layout']);
            $T->setHTML($app['layout.html.transforms']);
            if($app["debug"]) $T->setDashboard($app['layout.dashboard.transforms']);
            $T->setjQuery($app['layout.jquery.transforms']);
            return $T;
        });
    }
    /**
     *
     * @param Application $app Silex application instance.
     * @return void
     **/
    public function boot(Application $app)
    {
        $app['controllers_factory'];
        if ($app['assets.fs']) {
            $app['assets.fs']->register(__DIR__.'/assets/');
            $app['assets.fs']->register(__DIR__.'/templates/');
        }

        if (isset($app['layout'])) {
            $app['layout']->registerImgPattern('blog_detail', '/images/blog/detail/:id.jpg');
            $app['layout']->registerImgPattern('blog_list', '/images/blog/list/:id.jpg');
        }
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $app->get( '/',
            $app['myblog.controllers']->home()
        )->bind ('home');

        $app->get( '/blog/{id}',
            $app['myblog.controllers']->detail('blog_entry.add_comment')
        )->bind ('blog_entry');

        $app->get( '/blog/{id}/blog_detail_comments',
            $app['myblog.controllers']->detail('blog_entry.add_comment')
        )->bind ('blog_entry.detail_comments');

        $app->get( '/blog/{id}/add_comment',
            $app['myblog.controllers']->postComment()
        )->bind ('blog_entry.add_comment');

        return $controllers;
    }
}