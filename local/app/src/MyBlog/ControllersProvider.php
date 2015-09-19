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
            return new Controllers();
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