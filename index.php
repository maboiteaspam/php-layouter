<?php
error_reporting(E_ALL ^ E_STRICT); // it is really undesired to respect strict standard for friendly coding.



require 'vendor/autoload.php';

//exec('rm -fr run/data*');
//exec('rm -fr run/*php');
$AppController = new C\Foundation\AppController();

$app = $AppController->getApp([
//    'env' => 'prod',
    'assets.concat' => false,
    'debug' => true,
    'projectPath' => __DIR__,
]);

$myBlogModule = new MyBlog\Module();
$cBlogDataModule = new C\BlogData\Module();
$cBlogModule = new C\Blog\Module();
$cModule = new C\Module();

$cModule->register($app);
$cBlogDataModule->register($app);
$cBlogModule->register($app);
$myBlogModule->register($app);

$app['dispatcher']->dispatch('c_modules_loaded');


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
