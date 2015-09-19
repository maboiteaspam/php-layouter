<?php
namespace C\Blog;

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
        $app['blog.controllers'] = $app->share(function() use ($app) {
            return new Controllers($app['blogdata.entry'], $app['blogdata.comment']);
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
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $app->get( '/',
            $app['blog.controllers']->home()
        )->bind ('home');

        $app->get( '/blog/{id}',
            $app['blog.controllers']->detail('blog_entry.add_comment')
        )->bind ('blog_entry');

        $app->get( '/blog/{id}/blog_detail_comments',
            $app['blog.controllers']->detail('blog_entry.add_comment')
        )->bind ('blog_entry.detail_comments');

        $app->get( '/blog/{id}/add_comment',
            $app['blog.controllers']->postComment()
        )->bind ('blog_entry.add_comment');

        return $controllers;
    }
}