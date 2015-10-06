<?php
namespace C\Layout;

use C\TagableResource\ResourceTagger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use C\Misc\Utils;

class TaggedLayoutResponder extends LayoutResponder{
    /**
     * @var ResourceTagger
     */
    public $tagger;
    public function setTagger(ResourceTagger $tagger){
        $this->tagger = $tagger;
    }
    public function respond(Layout $layout, Request $request, Response $response=null){

        Utils::stderr('rendering layout');

        if (!$response) $response = new Response();

        $layout->emit('controller_build_finish', $response);
        $content = $layout->render();
        $layout->emit('layout_build_finish', $response);

        $TaggedResource = $layout->getTaggedResource();
        if ($TaggedResource===false) {
            Utils::stderr('this layout prevents caching');
            // this layout contains resource which prevent from being cached.
            // we shall not let that happen.
        } else {
//            $etag = $app['httpcache.tagger']->sign($TaggedResource);
            $this->tagger->setTaggedResource($TaggedResource);
            $etag = $this->tagger->sign();

            Utils::stderr('response is tagged with '.$etag);
            $response->setProtocolVersion('1.1');
            $response->setETag($etag);

            $response->setPublic();
            $response->setSharedMaxAge(60);
//                    $response->setMaxAge(60*10);
        }


        if (!$response->isNotModified($request)) {
            Utils::stderr('response is modified ');
            $response->setContent($content);
        }

        return $response;
    }
}