<?php
require 'vendor/autoload.php';

use C\LayoutBuilder\Layout\Layout;

use C\AppController\Silex as AppController;
use MyBlog\Controller as BlogController;

$AppController = new AppController();

$app = $AppController->getApp([
    'debug'=>true,
]);

$app['layout'] = new Layout([
    'debug'         => $app['debug'],
    'dispatcher'    => $app['dispatcher'],
    'helpers'       => $AppController->getHelpers($app),
    'imgUrls'       => [
        'blog_detail' => '/images/blog/detail/:id.jpg',
        'blog_list' => '/images/blog/list/:id.jpg',
    ],
]);


use Illuminate\Database\Capsule\Manager as Capsule;
$capsule = new Capsule;
$capsule->addConnection([
    'driver'   => 'sqlite',
    'database' => ':memory:',
    'prefix'   => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
]);
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();
$builder = $capsule->getConnection()->getSchemaBuilder();
$builder->create('blog_entry', function($table) {
    $table->increments('id');
    $table->string('title');
    $table->string('author');
    $table->string('img_alt');
    $table->string('content');
    $table->enum('status', array('VISIBLE', 'HIDDEN'));
    $table->timestamps();
});
$builder->create('blog_comment', function($table) {
    $table->increments('id');
    $table->string('author');
    $table->string('content');
    $table->enum('status', array('VISIBLE', 'HIDDEN'));
    $table->timestamps();
    $table->integer('blog_entry_id');
});

$fixtureEntries = include(__DIR__ . '/local/app/src/MyBlog/fixtures/blog-entries.php');
foreach ($fixtureEntries as $entry) {
    $comments = $entry['comments'];
    unset($entry['comments']);
    $id = Capsule::table('blog_entry')->insertGetId($entry);
    foreach ($comments as $comment) {
        $comment['blog_entry_id'] = $id;
        Capsule::table('blog_comment')->insert($comment);
    }
}



$blog = new BlogController();

$app->get( '/',
    $blog->home()
)->bind ('home');

$app->get( '/blog/{id}',
    $blog->detail()
)->bind ('blog_entry');

$app->get( '/blog/{id}/blog_detail_comments',
    $blog->detail()
)->bind ('blog_entry_detail_comments');

$app->run();
