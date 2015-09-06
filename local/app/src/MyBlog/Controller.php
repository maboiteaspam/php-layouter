<?php

namespace MyBlog;

use C\Data\Eloquent;
use C\ProductionLine\Pipeline;

use C\AppController\Silex as AppController;

use C\AppController\Controller as BaseController;
use C\HTMLLayoutBuilder\Transforms as HTMLTransforms;
use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;

use C\Blog\Transforms as BlogLayout;
use MyBlog\Transforms as MyBlogLayout;

use Symfony\Component\HttpFoundation\Request;

use Illuminate\Database\Capsule\Manager as Capsule;

class Controller extends BaseController{

    public function home() {
        $layout = $this->layout;
        return function (Request $request) use($layout) {
            Pipeline::passThrough(HTMLTransforms::createBase())
                ->pipe(BlogLayout::home())
                ->pipe(MyBlogLayout::home(
                    Eloquent::get(Capsule::table('blog_entry')->take(20)->orderBy('created_at', 'DESC')),
                    Eloquent::get(Capsule::table('blog_comment')->take(5)->orderBy('created_at', 'DESC'))
                ))
                ->pipe(MyBlogLayout::baseTemplate())
                ->write($layout);
            return AppController::respondLayout($request, $layout);
        };
    }

    public function detail() {
        $layout = $this->layout;
        return function (Request $request, $id) use($layout) {
            $urlFor = $layout->config['helpers']['urlFor'];

            Pipeline::passThrough(HTMLTransforms::createBase())
                ->pipe(BlogLayout::detail())
                ->pipe(MyBlogLayout::detail(
                    Eloquent::first(Capsule::table('blog_entry')->where('id', '=', $id)),
                    Eloquent::get(Capsule::table('blog_comment')->where('blog_entry_id', '=', $id)),
                    Eloquent::get(Capsule::table('blog_comment')->where('blog_entry_id', '!=', $id)->take(5)->orderBy('created_at','DESC'))
                ))
                ->pipe(jQueryTransforms::inject('page_footer_js'))
                ->pipe(jQueryTransforms::ajaxify('blog_detail_comments', [
                    'isAjax'=> $request->isXmlHttpRequest(),
                    'url'   => $urlFor($request->get('_route'), $request->get('_route_params'))
                ] ))
                ->pipe(MyBlogLayout::baseTemplate())
                ->write($layout);
            return AppController::respondLayout($request, $layout);
        };
    }
}