<?php
namespace C\Provider;

use C\Esi\TokenViewHelper;
use C\Layout\Layout;
use C\Misc\Utils;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use C\Esi\Token;

class EsiServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        // esi support
        // https://www.varnish-cache.org/trac/wiki/ESIfeatures
        // https://www.varnish-software.com/book/3/Content_Composition.html#edge-side-includes
        // https://www.varnish-cache.org/docs/3.0/tutorial/esi.html
        // http://blog.lavoie.sl/2013/08/varnish-esi-and-cookies.html
        // http://symfony.com/doc/current/cookbook/cache/varnish.html
        // http://silex.sensiolabs.org/doc/providers/http_cache.html
        // https://github.com/serbanghita/Mobile-Detect
        // http://symfony.com/doc/current/cookbook/cache/form_csrf_caching.html

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
        if (isset($app['layout.fs'])) {
            $app['layout.fs']->register(__DIR__.'/../Esi/templates/', 'Esi');
        }
        $app->before(function(Request $request, Application $app){

            if (!$request->isXmlHttpRequest()) {
                $secret = $app['esi.secret'];

                if ($request->headers->get("x-esi-secret") && $request->query->has("target")) {
                    $rsecret = $request->headers->get("x-esi-secret");
                    if ($rsecret===$secret) {
                        Utils::stderr("slave found");
                        $app['layout']->requestMatcher->setRequestKind('esi-slave');
                    } else {
                        Utils::stderr("esi secret mismatch");
                        Utils::stderr("request secret was $rsecret");
                    }

                } else if ($request->headers->has("Surrogate-Capability")) {
                    $app['layout']->requestMatcher->setRequestKind('esi-master');
                    $app['layout']->onLayoutBuildFinish(function ($ev, $layout, $response) {
                        $response->headers->set("Surrogate-Control", "ESI/1.0");
                    });
                }
            }
        });

    }
}