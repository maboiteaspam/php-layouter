<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class RepositoryServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
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
            // this will resolve a repository data proxy.
            // the repository data records methods calls,
            // this resolver executes those methods calls on-delayed-demand.
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->tagDataWith('repository', function ($data) use($app) {
                $repositoryName = $data[0];
                $method = $data[1];
                $repository = $app[$repositoryName];
                $v = call_user_func_array([$repository, $method[0]], $method[1]);
                return $v;
            });
        }
    }
}