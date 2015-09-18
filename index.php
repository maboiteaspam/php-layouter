<?php
error_reporting(E_ALL ^ E_STRICT); // it is really undesired to respect strict standard for friendly coding.

require 'vendor/autoload.php';

//exec('rm -fr run/data*');
//exec('rm -fr run/*php');
$AppController = new C\Foundation\AppController([
//    'env' => 'prod',
    'assets.concat' => false,
//    'debug' => true,
    'projectPath' => __DIR__,
]);

if ($AppController->builtinServer("www")) return ;

$AppController->setupApplication(function($AppController){
    $AppController->register(new C\Module());
    $AppController->register(new C\BlogData\Module());
    $AppController->register(new C\Blog\Module());
    $AppController->register(new MyBlog\Module());
});


$AppController->runWebApplication(function($app){
    $blog = new MyBlog\Controller();

    $app->get( '/',
        $blog->home()
    )->bind ('home');

    $app->get( '/blog/{id}',
        $blog->detail('blog_entry_add_comment')
    )->bind ('blog_entry');

    $app->get( '/blog/{id}/blog_detail_comments',
        $blog->detail('blog_entry_add_comment')
    )->bind ('blog_entry_detail_comments');

    $app->get( '/blog/{id}/add_comment',
        $blog->postComment()
    )->bind ('blog_entry_add_comment');

    $app->run();
});
