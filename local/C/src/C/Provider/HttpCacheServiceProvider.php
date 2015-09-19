<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use C\TagableResource\ResourceTagger;
use C\TagableResource\Cache\Store;

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

        if (!isset($app['httpcache.store_name']))
            $app['httpcache.store_name'] = "http-store";

        $app['httpcache.store'] = $app->share(function() use($app) {
            $storeName = $app['httpcache.store_name'];
            $cache = $app['caches'][$storeName];
            $store = new Store($cache);
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
        if (isset($app['httpcache.tagger'])) {
            $tagger = $app['httpcache.tagger'];
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->tagDataWith('repository', function ($data) use($app) {
                $repositoryName = $data[0];
                $method = $data[1];
                $repository = $app[$repositoryName];
                $v = call_user_func_array([$repository, $method[0]], $method[1]);
                return $v;
            });
        }

        $app->before(function (Request $request, Application $app) {
            if ($request->isMethodSafe()) {
                $tagger = $app['httpcache.tagger'];
                $store = $app['httpcache.store'];
                $checkFreshness = $app['httpcache.check_taged_resource_freshness'];
                $etags = $request->getETags();

                foreach ($etags as $etag) {
                    if (!in_array($etag, ['*'])) {
                        $etag = str_replace(['"',"'"], '', $etag);
                        $res = $store->getResource($etag);
                        if ($res) {
                            if (!$checkFreshness || $checkFreshness && $tagger->isFresh($res)) {
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