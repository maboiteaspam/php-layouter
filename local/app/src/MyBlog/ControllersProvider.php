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
            $controllers = new Controllers('blogdata.entry', 'blogdata.comment');
            return $controllers;
        });
    }
    /**
     *
     * @param Application $app Silex application instance.
     * @return void
     **/
    public function boot(Application $app)
    {
        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(__DIR__.'/assets/', 'MyBlog');
        }
        if (isset($app['intl.fs'])) {
            $app['intl.fs']->register(__DIR__.'/intl/', 'MyBlog');
        }
        if (isset($app['layout.fs'])) {
            $app['layout.fs']->register(__DIR__.'/templates/', 'MyBlog');
        }
        if (isset($app['modern.fs'])) {
            $app['modern.fs']->register(__DIR__.'/layouts/', 'MyBlog');
        }
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get( '/',
            $app['myblog.controllers']->home()
        )->bind ('home');

        $controllers->get( '/blog/{id}',
            $app['myblog.controllers']->detail('blog_entry.add_comment')
        )->bind ('blog_entry');

        $controllers->get( '/blog/{id}/blog_detail_comments',
            $app['myblog.controllers']->detail('blog_entry.add_comment')
        )->bind ('blog_entry.detail_comments');

        $controllers->post( '/blog/{id}/add_comment',
            $app['myblog.controllers']->postComment()
        )->bind ('blog_entry.add_comment');

        return $controllers;
    }
}