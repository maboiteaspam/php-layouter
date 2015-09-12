<?php
require 'vendor/autoload.php';

//exec('rm -fr run/data*');
//exec('rm -fr run/assets_*');
if (!is_dir("run/")) mkdir("run/");

$AppController = new C\Foundation\AppController();

$app = $AppController->getApp([
    'debug' => true,
    'server_type' => 'builtin',
    'projectPath' => __DIR__,
    'documentRoot' => __DIR__.'/www/',
    'private_build_dir' => __DIR__.'/run/',
    'public_build_dir' => __DIR__.'/www/run/',
    'assets.concat' => false,
]);

$myBlogModule = new MyBlog\Module();
$cBlogModule = new C\Blog\Module();
$cModule = new C\Module();

$cModule->register($app);
$cBlogModule->register($app);
$myBlogModule->register($app);

$schemaIsFresh = false;
if ($AppController->isEnv('dev')) {
    $schemaIsFresh = $app['schema_loader']->isFresh();
    $exists = $app['capsule.exists'];
    if ($app['capsule.exists']() && !$schemaIsFresh) {
        $app['capsule.delete']();
    }
}

if ($AppController->isEnv('dev')) {
    $app['capsule.connection.dev']();
    $app['capsule']->bootEloquent();
    $app['capsule']->setAsGlobal();
}

if ($AppController->isEnv('dev')) {
    if (!$app['capsule.exists']() || !$schemaIsFresh) {
        $app['schema_loader']->build();
        $app['schema_loader']->populate();
    }
}


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
