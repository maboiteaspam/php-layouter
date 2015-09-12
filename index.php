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
    'documentRoot' => 'www/',
    'private_build_dir' => __DIR__.'/run/',
    'public_build_dir' => __DIR__.'/www/run/',
    'assets.concat' => false,
]);
$appCtx = $AppController->appCtx;

$blogModule = new MyBlog\Module();
$cModule = new C\Module();

$cModule->register($appCtx);
$blogModule->register($appCtx);

$schemaIsFresh = false;
if ($AppController->isEnv('dev')) {
    $schemaIsFresh = $appCtx['schema_loader']->isFresh();
    $exists = $appCtx['capsule.exists'];
    $delete = $appCtx['capsule.delete'];
    if ($exists() && !$schemaIsFresh) {
        $delete();
    }
}

if ($AppController->isEnv('dev')) {
    $dbConn = $appCtx['capsule.connection.dev'];
    $dbConn();
    $appCtx['capsule']->bootEloquent();
    $appCtx['capsule']->setAsGlobal();
}

if ($AppController->isEnv('dev')) {
    if (!$appCtx['capsule.exists']() || !$schemaIsFresh) {
        $appCtx['schema_loader']->build();
        $appCtx['schema_loader']->populate();
    }
}


$blog = new MyBlog\Controller();

$app->get( '/',
    $blog->home($appCtx)
)->bind ('home');

$app->get( '/blog/{id}',
    $blog->detail($appCtx, 'blog_entry_add_comment')
)->bind ('blog_entry');

$app->get( '/blog/{id}/blog_detail_comments',
    $blog->detail($appCtx, 'blog_entry_add_comment')
)->bind ('blog_entry_detail_comments');

$app->get( '/blog/{id}/add_comment',
    $blog->postComment($appCtx)
)->bind ('blog_entry_add_comment');

$app->run();
