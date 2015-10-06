<?php
namespace C\Layout;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use C\Misc\Utils;

class LayoutResponder {
    public function respond(Layout $layout, Request $request, Response $response=null){
        Utils::stderr('response is new '.$request->getUri());
        if (!$response) $response = new Response();
        $layout->emit('controller_build_finish', $response);
        $content = $layout->render();
        $response->setContent($content);
        $layout->emit('layout_build_finish', $response);
        return $response;
    }
}