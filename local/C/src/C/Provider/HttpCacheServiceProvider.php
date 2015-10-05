<?php
namespace C\Provider;

use C\Misc\Utils;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use C\TagableResource\ResourceTagger;
use C\HTTP\Cache\Store;

class HttpCacheServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        $app['httpcache.tagger'] = $app->share(function() {
            return new ResourceTagger();
        });

        if (!isset($app['httpcache.cache_store_name']))
            $app['httpcache.cache_store_name'] = "http-store";

        $app['httpcache.store'] = $app->share(function(Application $app) {
            $storeName = $app['httpcache.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            $store = new Store('httpcache-', $cache);
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
        $app->before(function (Request $request, Application $app) {
            Utils::stderr('-------------');
            Utils::stderr('receiving url '.$request->getUri());
        }, Application::EARLY_EVENT);

        // before the app is executed, we should check the cache
        // and try to take a shortcut.
        $app->before(function (Request $request, Application $app) {
            if ($request->isMethodSafe()) {
                /* @var $tagger ResourceTagger */
                /* @var $store Store */
                $tagger = $app['httpcache.tagger'];
                $store = $app['httpcache.store'];
                $checkFreshness = $app['httpcache.check_taged_resource_freshness'];
                $etags = $request->getETags();

                $respondEtagedResource = function ($etag) use($store, $tagger, $checkFreshness) {
                    $res = $store->getResource($etag);
                    if ($res) {
                        Utils::stderr('found resource for etag: '.$etag);
                        $originalTag = $res->originalTag;
                        $fresh = $checkFreshness && $tagger->isFresh($res);
                        if ($fresh) {
                            $content = $store->getContent($etag);
                            $body = $content['body'];
                            $response = new Response();
                            $response->headers->replace($content['headers']);
                            $response->setProtocolVersion('1.1');
                            $response->setContent($body);
                            $response->headers->set("X-CACHED", "true");
                            Utils::stderr('responding from cache a content length ='.strlen($body));
                            Utils::stderr('headers ='.json_encode($content['headers']));
                            return $response;

                        } else {
                            Utils::stderr('is etag fresh:'.json_encode($fresh));
                            Utils::stderr('original Tag:'.json_encode($originalTag));
                            Utils::stderr('new Tag:'.json_encode($res->originalTag));
                            Utils::stderr('require fresh:'.json_encode($checkFreshness));
                        }
                    } else {
                        Utils::stderr("etag does not exists in cache");
                    }
                    return false;
                };


                // when the request is sent by user
                // it may contain an if-none-match: header
                // which means the user is looking for an url page he already seensbefore,
                // he knows its etag.
                // We should check the cache to know how to handle this request
                // in the best response time possible.
                $hasFoundAnyResource = false;
                foreach ($etags as $etag) {
                    if (!in_array($etag, ['*'])) {
                        $etag = str_replace(['"',"'"], '', $etag);
                        Utils::stderr("check etag {$etag} for uri {$request->getUri()}");
                        $resultResponse = $respondEtagedResource($etag);
                        $hasFoundAnyResource = true;
                        if ($resultResponse!==false) {
                            Utils::stderr("found valid etag");
                            $resultResponse->setNotModified();
                            return $resultResponse;
                        }
                    }
                }

                // there request have no etag.
                if(count($etags)) {
                    Utils::stderr('request has etag but '.
                        (!$hasFoundAnyResource?
                            'there is no cache to serve':
                            'there is some keys, but they are outdated..'
                        ));
                }
                else Utils::stderr('no etag in this request');

                // here can exists a FPC cache layer.
                // using url+ua+lang+request kind.
                if(!count($etags) && false) {
                    // @todo check if resource explicitly wants fresh version
                    // when user press ctl+f5, it sends request with max-age=0 (+/-),
                    // it means the user wants fresh version of the document.
                    // so we should not call cache here.
                    $knownEtag = $store->getEtag($request->getUri());
                    if ($knownEtag) {
                        Utils::stderr('yeah, we found a matching known etag for this url');
                        // @todo this must check vary by headers (lang / UA)
                        $resultResponse = $respondEtagedResource($knownEtag);
                        if ($resultResponse!==false) {
                            Utils::stderr('youpi it works');
                            return $resultResponse;
                        } else {
                            Utils::stderr('erf, we can t use it...');
                        }
                    }
                }
                //$request->getUri()
            }
            return null;
        }, Application::LATE_EVENT);


        // once app has finished,
        // let s check if the response is cache-able,
        // not a cached response itself,
        // and using safe method.
        // in that case, lets record that into the cache store.
        $app->after(function (Request $request, Response $response, Application $app) {

            Utils::stderr('is response cache-able '.var_export($response->isCacheable(), true));

            if ($request->isMethodSafe()
                && $response->isCacheable()
                && !$response->getStatusCode()!==304
                && !$response->headers->has("X-CACHED")
                && $app["httpcache.taggedResource"]) {
                $etag = $response->getEtag();
                Utils::stderr('saving resource '.$request->getUri());
                Utils::stderr(' etag '.$etag);
                if ($etag) {
                    $headers = $response->headers->all();
                    // those are headers to save into cahce.
                    // later when the cache is served, they are re injected.
                    $headers = Utils::arrayPick($headers, [
                        'cache-control', 'etag', 'last-modified', 'expires',
                        'date',
                        'Surrogate-Capability',
                    ]);
                    $app["httpcache.store"]->store(
                        $app["httpcache.taggedResource"],
                        $request->getUri(), [
                        'headers'   => $headers,
                        'body'      => $response->getContent()
                    ]);
                    Utils::stderr('headers ='.json_encode($headers));
                }
            }
            $response->headers->remove("X-CACHED");
        }, Application::LATE_EVENT);


    }
}