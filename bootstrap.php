<?php

#region runtime configuration update
$runtime = [
    'debug'                 => !true,
    'env'                   => getenv('APP_ENV') ? getenv('APP_ENV') : 'dev',
    'project.path'          => __DIR__,
    'run.path'              => __DIR__.'/run/',
//    'security.firewalls'    => [],
];
$configTokens = [
    'env',
    'run.path',
    'project.path',
];
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

$tokens = [];
foreach ($configTokens as $configToken) {
    $tokens[$configToken] = $runtime[$configToken];
}

foreach( $runtime as $key=>$value ){
    $app[$key] = $value;
}

$app->register(new \Igorw\Silex\ConfigServiceProvider(__DIR__ . "/config.php", $tokens));
#endregion


#region service providers


$app->register(new \Moust\Silex\Provider\CacheServiceProvider(), [
    'caches.default' => 'default',
    'caches.options' => array_merge([
        'default'=>[
            'driver' => 'array',
        ],
    ], $app['caches.options']),
]);

//$app['cache.drivers'] = $app->extend('cache.drivers', function ($drivers) {
//    $drivers[] = '\\Moust\\Silex\\Cache\\WincacheCache';
//    return $drivers;
//});

//$app->register(new MonologServiceProvider([
//]));
//$app->register(new SessionServiceProvider( ));
//$app->register(new SecurityServiceProvider([
//]));
//$app->register(new RememberMeServiceProvider([
//]));

$app->register(new \Silex\Provider\TranslationServiceProvider( ));
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new \Silex\Provider\ValidatorServiceProvider());
$app->register(new \Silex\Provider\FormServiceProvider());

$app->register(new \C\Provider\IntlServiceProvider());
$app->register(new \C\Provider\AssetsServiceProvider());
$app->register(new \C\Provider\HttpCacheServiceProvider());
$app->register(new \C\Provider\CapsuleServiceProvider());
$app->register(new \C\Provider\RepositoryServiceProvider());
$app->register(new \C\Provider\LayoutServiceProvider());
$app->register(new \C\Provider\ModernAppServiceProvider());
$app->register(new \Binfo\Silex\MobileDetectServiceProvider());
#endregion



#region controllers providers
$app->register(new \C\BlogData\ServiceProvider());
$blogController = new C\Blog\ControllersProvider();
$myBlogController = new \MyBlog\ControllersProvider();
$formDemo = new \C\FormDemo\ControllersProvider();
$app->register($blogController);
$app->register($myBlogController);
$app->register($formDemo);
#endregion


return $app;
