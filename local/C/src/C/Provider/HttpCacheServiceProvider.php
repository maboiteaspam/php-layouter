<?php
namespace C\Provider;

use C\FS\LocalFs;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use C\HttpCache\ResourceTagger;
use C\HttpCache\Store;

class HttpCacheServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        $app['httpcache.tagger'] = $app->share(function() use($app) {
            $tagger = new ResourceTagger();
            return $tagger;
        });

        if (!isset($app['httpcache.store_path']))
            $app['httpcache.store_path'] = "run/http/";

        $app['httpcache.store'] = $app->share(function() use($app) {
            $store = new Store();
            $store->setStorePath($app['httpcache.storepath']);
            return $store;
        });
        $app['httpcache.taggedResource'] = null;
    }
    /**
     * Boot the Capsule service.
     *
     * @param Application $app Silex application instance.
     *
     * @return void
     **/
    public function boot(Application $app)
    {
        $app["dispatcher"]->addListener('init.app', function() use($app) {
            if (!LocalFs::is_dir($app['httpcache.store_path']))
                LocalFs::mkdir($app['httpcache.store_path'], 0700, true);
        });

        $app->before(function (Request $request, Application $app) {
            if ($request->isMethodSafe()) {

                $tagger = $app['httpcache.tagger'];
                $store = $app['httpcache.store'];
                $etags = $request->getETags();

                foreach ($etags as $etag) {
                    if (!in_array($etag, ['*'])) {
                        $etag = str_replace(['"',"'"], '', $etag);
                        $res = $store->getResource($etag);
                        if ($res) {
                            if ($tagger->isFresh($res)) {
                                $content = $store->getContent($etag);
                                $body = $content['body'];
                                $response = new Response();
                                $response->headers->replace($content['headers']);
                                $response->setProtocolVersion('1.1');
                                $response->setContent($body);
                                $response->setNotModified();
                                $response->headers->set("X-CACHED", "true");
                                return $response;
                            }
                        }
                    }
                }
            }
        });

        $app->after(function (Request $request, Response $response, Application $app) {
            if ($request->isMethodSafe()
                && !$response->getStatusCode()!==304
                && !$response->headers->has("X-CACHED")
                && $app["httpcache.taggedResource"]) {
                $etag = $response->getEtag();
                if ($etag) {
                    $headers = $response->headers->all();
                    $app["httpcache.store"]->store(
                        $app["httpcache.taggedResource"], [
                        'headers'   => $headers,
                        'body'      => $response->getContent()
                    ]);
                }
            }
            $response->headers->remove("X-CACHED");
        });
    }
}