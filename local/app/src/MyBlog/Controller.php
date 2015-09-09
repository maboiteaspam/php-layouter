<?php

namespace MyBlog;

use C\Data\Eloquent;

use C\AppController\Silex as AppController;

use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;

use C\DebugLayoutBuilder\Transforms as debugTransforms;
use MyBlog\Transforms as MyBlogLayout;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

class Controller{

    public function home() {
        return function (Application $app, Request $request) {
            MyBlogLayout::transform($app)
                ->baseTemplate()
                ->home(
                    Eloquent::delayed('blog_entry')->take(20)->orderBy('created_at', 'DESC')->all(),
                    Eloquent::delayed('blog_comment')->take(5)->orderBy('created_at', 'DESC')->all()
                )->then(
                    debugTransforms::transform($app)->debug(__CLASS__)
                )
                ->finalize([
                    'concat'=>false,
                ]);
            return AppController::respond($app, $request);
        };
    }

    public function detail() {
        return function (Application $app, Request $request, $id) {
            $urlFor = $app['layout']->config['helpers']['urlFor'];

            MyBlogLayout::transform($app)
                ->baseTemplate()
                ->detail(
                    Eloquent::delayed('blog_entry')->where('id', '=', $id)->one(),
                    Eloquent::delayed('blog_comment')->where('blog_entry_id', '=', $id)->take(5)->orderBy('created_at','DESC')->all(),
                    Eloquent::delayed('blog_comment')->where('blog_entry_id', '!=', $id)->take(5)->orderBy('created_at','DESC')->all()
                )->then(
                    jQueryTransforms::transform($app)->ajaxify('blog_detail_comments', [
                        'isAjax'=> $request->isXmlHttpRequest(),
                        'url'   => $urlFor($request->get('_route'), $request->get('_route_params'))
                    ])
                )->then(
                    debugTransforms::transform($app)->debug(__CLASS__)
                )
                ->finalize([
                    'concat'=>false,
                ]);
            return AppController::respond($app, $request);
        };
    }
}