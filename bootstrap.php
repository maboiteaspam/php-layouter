<?php
error_reporting(E_ALL ^ E_STRICT); // it is really undesired to respect strict standard for friendly coding.


#region runtime configuration update
$runTimeOverride = [
    'debug'=> true,
    'env'=>'prod',
    'monolog.logfile' => __DIR__.'/run/development.log',
    'security.firewalls' => [],
];
$configTokens = [];
#endregion


#region silex
require 'vendor/autoload.php';
use \Silex\Application;
$app = new Application();
#endregion


#region error to exception
// sometimes it s useful to register it to get a stack trace
function exception_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // Ce code d'erreur n'est pas inclu dans error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");
#endregion


#region config

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


$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . "/config.php", $configTokens));

foreach( $runTimeOverride as $key=>$value ){
    $app[$key] = $value;
}
#endregion


#region service providers

$app->register(new Moust\Silex\Provider\CacheServiceProvider(), array(
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

$app->register(new Silex\Provider\TranslationServiceProvider( ));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());

$app->register(new C\Provider\AssetsServiceProvider());
$app->register(new C\Provider\HttpCacheServiceProvider());
$app->register(new C\Provider\CapsuleServiceProvider());
$app->register(new \C\Provider\RepositoryServiceProvider());
$app->register(new C\Provider\LayoutServiceProvider());
$app->register(new \C\Provider\ModernAppServiceProvider());
$app->register(new \C\BlogData\ServiceProvider());
#endregion



#region controllers providers
$blogController = new C\Blog\ControllersProvider();
$myBlogController = new MyBlog\ControllersProvider();
$app->register($blogController);
$app->register($myBlogController);
#endregion


return $app;
