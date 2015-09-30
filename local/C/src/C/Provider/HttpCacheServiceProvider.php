<?php
namespace C\Provider;

use C\HTTP\RequestProxy;
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
        $app['httpcache.request'] = $app->share(function(Application $app) {
            /* @var $request Request */
            $request = $app['request'];
            return new RequestProxy($request);
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
                        $fresh = $tagger->isFresh($res);
                        if (!$checkFreshness || $checkFreshness && $fresh) {
                            $content = $store->getContent($etag);
                            $body = $content['body'];
                            $response = new Response();
                            $response->headers->replace($content['headers']);
                            $response->setProtocolVersion('1.1');
                            $response->setContent($body);
                            $response->headers->set("X-CACHED", "true");
                            Utils::stderr('responding from cache a content length ='.strlen($body));
                            Utils::stderr('headers ='.var_export($content['headers'], true));
                            return $response;
                        } else {
                            Utils::stderr('is etag fresh:'.var_export($fresh, true));
                            Utils::stderr('original Tag:'.var_export($originalTag, true));
                            Utils::stderr('new Tag:'.var_export($res->originalTag, true));
                            Utils::stderr('require fresh:'.var_export($checkFreshness, true));
                        }
                    }
                    return false;
                };

                Utils::stderr('-------------');
                Utils::stderr('check etag for uri '.$request->getUri());
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
                        $resultResponse = $respondEtagedResource($etag);
                        $hasFoundAnyResource = true;
                        if ($resultResponse!==false) {
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
                    ]);
                    $app["httpcache.store"]->store(
                        $app["httpcache.taggedResource"],
                        $request->getUri(), [
                        'headers'   => $headers,
                        'body'      => $response->getContent()
                    ]);
                    Utils::stderr('headers ='.var_export($headers, true));
                }
            }
            $response->headers->remove("X-CACHED");
        }, Application::LATE_EVENT);
    }
}