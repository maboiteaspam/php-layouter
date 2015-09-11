<?php
require 'vendor/autoload.php';


use C\Foundation\AppController;
use C\Blog\Schema as BlogSchema;
use MyBlog\Controller as BlogController;

$AppController = new AppController();

$app = $AppController->getApp([
    'debug'=>true,
]);
$layout = $AppController->getLayout([
    'imgUrls'       => [
        'blog_detail'   => '/images/blog/detail/:id.jpg',
        'blog_list'     => '/images/blog/list/:id.jpg',
    ]
]);

$database = __DIR__.'/database.sqlite';
$isNewDb = !file_exists($database);
touch($database);
$capsule = $AppController->getDatabase([
    'driver'   => 'sqlite',
    'database' => $database,
    'prefix'   => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',

]);
$app['layout'] = $layout;
$app['capsule'] = $capsule;

if ($isNewDb) {
    $blogSchema = new BlogSchema();
    $blogSchema->setup();
    $blogSchema->load();
}

$AppController->registerAssetsPaths(getcwd(), 'builtin', [
    'local/app/src/MyBlog/assets/',
    'local/C/src/C/DebugLayoutBuilder/assets/',
    'local/C/src/C/jQueryLayoutBuilder/assets/',
]);

$blog = new BlogController();

$app->get( '/',
    $blog->home()
)->bind ('home');

$app->get( '/blog/{id}',
    $blog->detail('blog_entry_add_comment')
)->bind ('blog_entry');

$app->get( '/blog/{id}/add_comment',
    $blog->postComment()
)->bind ('blog_entry_add_comment');

$app->get( '/blog/{id}/blog_detail_comments',
    $blog->detail('blog_entry_add_comment')
)->bind ('blog_entry_detail_comments');

$app->run();
