<?php
error_reporting(E_ALL ^ E_STRICT); // it is really undesired to respect strict standard for friendly coding.


$runTimeOverride = [
    'debug'=> true,
//    'env'=>'prod',
    'monolog.logfile' => __DIR__.'/run/development.log',
    'security.firewalls' => [],
];
$configTokens = [];




require 'vendor/autoload.php';

use \Silex\Application;
use \Silex\Provider\UrlGeneratorServiceProvider;

use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;

use Moust\Silex\Provider\CacheServiceProvider;

use C\Provider\CapsuleServiceProvider;
use C\Provider\LayoutServiceProvider;
use C\Provider\AssetsServiceProvider;
use C\Provider\HttpCacheServiceProvider;
use C\Provider\CServiceProvider;

use Igorw\Silex\ConfigServiceProvider;

$app = new Application();

function exception_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // Ce code d'erreur n'est pas inclu dans error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");


$defaultConfig = [
    'env'                   => getenv('APP_ENV') ? getenv('APP_ENV') : 'dev',
    'projectPath'           => __DIR__,
    'caches.options'        => [],
    'caches.config'         => [],
    'httpcache.store_name'  => 'http-store'
];
$runTimeOverride = array_merge($defaultConfig, $runTimeOverride);

$configTokens = array_merge([
    'env'           => $runTimeOverride['env'],
    'projectPath'   => $runTimeOverride['projectPath'],
], $configTokens);

$app->register(new ConfigServiceProvider(__DIR__ . "/config.php", $configTokens));

foreach( $runTimeOverride as $key=>$value ){
    $app[$key] = $value;
}

$app->register(new CacheServiceProvider(), array(
    'caches.default' => 'http-store',
    'cache.options' => [
        'http-store'=>[],
    ],
    'caches.options' => array_merge([
        'http-store'=>[]], $app['caches.options']
    ),
    'caches.config' => array_merge([
        'http-store'=>['driver' => 'array']], $app['caches.config']
    ),
));

//$app->register(new MonologServiceProvider([
//]));
//$app->register(new SessionServiceProvider( ));
//$app->register(new SecurityServiceProvider([
//]));
//$app->register(new RememberMeServiceProvider([
//]));
$app->register(new TranslationServiceProvider( ));
$app->register(new UrlGeneratorServiceProvider());
$app->register(new FormServiceProvider());

$app->register(new AssetsServiceProvider());
$app->register(new HttpCacheServiceProvider());
$app->register(new CapsuleServiceProvider());
$app->register(new LayoutServiceProvider());

$app->register(new \C\BlogData\ServiceProvider());

$app->register(new CServiceProvider());

$blogController = new C\Blog\ControllersProvider();
$myBlogController = new MyBlog\ControllersProvider();

$app->register($blogController);
$app->register($myBlogController);
$app->mount('/', $myBlogController);

return $app;
