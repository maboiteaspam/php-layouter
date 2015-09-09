<?php
namespace C\AppController;

use \Silex\Application;
use \Silex\Provider\HttpCacheServiceProvider;
use \Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use C\Misc\Utils;

class Silex{
    public function getApp (array $values = array()) {
        $app = new Application($values);

        $app->register(new HttpCacheServiceProvider(), array(
            'http_cache.cache_dir' => __DIR__.'/cache/',
        ));
        $app->register(new UrlGeneratorServiceProvider());

        return $app;
    }

    public function getHelpers (Application $app) {
        return [
            'urlFor'=> function ($name, $options=[], $only=[]) use($app) {
                $options = Utils::arrayPick($options, $only);
                return $app['url_generator']->generate($name, $options);
            },
            'urlArgs'=> function ($data=[], $only=[]) use($app) {
                if (isset($this->meta['from'])) {
                    $data = array_merge(Utils::arrayPick($this->meta, ['from']), $data);
                }
                $data = Utils::arrayPick($data, $only);
                $query = http_build_query($data);
                return $query ? '?'.$query : '';
            }
        ];
    }

    public static function respond(Application $app, Request $request) {
        $response = new Response();

        $response->setETag($app['layout']->getEtag());
        $response->mustRevalidate(true);
        $response->setPrivate(true);

        if ($response->isNotModified($request)) {
            return $response;
        }
        $response->setContent($app['layout']->getContent($app['layout']->block));
        return $response;
    }
}
