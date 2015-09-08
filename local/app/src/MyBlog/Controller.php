<?php

namespace MyBlog;

use C\Data\Eloquent;

use C\AppController\Silex as AppController;

use C\AppController\Controller as BaseController;
use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;

use C\DebugLayoutBuilder\Transforms as debugTransforms;
use MyBlog\Transforms as MyBlogLayout;

use Symfony\Component\HttpFoundation\Request;

class Controller extends BaseController{

    public function home() {
        $layout = $this->layout;
        return function (Request $request) use($layout) {

            MyBlogLayout::transform($layout)
                ->baseTemplate()
                ->home(
                    Eloquent::delayed('blog_entry')->take(20)->orderBy('created_at', 'DESC')->all(),
                    Eloquent::delayed('blog_comment')->take(5)->orderBy('created_at', 'DESC')->all()
                )->then(
                    debugTransforms::transform($this->layout)->debug()
                )
                ->finalize([
                    'concat'=>false,
                ]);

            return AppController::respondLayout($request, $layout);
        };
    }

    public function detail() {
        $layout = $this->layout;
        return function (Request $request, $id) use($layout) {
            $urlFor = $layout->config['helpers']['urlFor'];

            MyBlogLayout::transform($layout)
                ->baseTemplate()
                ->detail(
                    Eloquent::delayed('blog_entry')->where('id', '=', $id)->one(),
                    Eloquent::delayed('blog_comment')->where('blog_entry_id', '=', $id)->take(5)->orderBy('created_at','DESC')->all(),
                    Eloquent::delayed('blog_comment')->where('blog_entry_id', '!=', $id)->take(5)->orderBy('created_at','DESC')->all()
                )->then(
                    jQueryTransforms::transform($layout)->ajaxify('blog_detail_comments', [
                        'isAjax'=> $request->isXmlHttpRequest(),
                        'url'   => $urlFor($request->get('_route'), $request->get('_route_params'))
                    ])
                )->then(
                    debugTransforms::transform($this->layout)->debug()
                )
                ->finalize([
                    'concat'=>false,
                ]);

            return AppController::respondLayout($request, $layout);
        };
    }
}