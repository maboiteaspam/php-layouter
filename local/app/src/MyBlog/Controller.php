<?php

namespace MyBlog;

use C\Data\Eloquent;

use C\AppController\Silex as AppController;

use C\AppController\Controller as BaseController;
use C\HTMLLayoutBuilder\Transforms as HTMLTransforms;
use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;

use C\Blog\Transforms as BlogLayout;
use MyBlog\Transforms as MyBlogLayout;

use Symfony\Component\HttpFoundation\Request;

class Controller extends BaseController{

    public function home() {
        $layout = $this->layout;
        return function (Request $request) use($layout) {
            HTMLTransforms::transform($layout)->createBase();
            BlogLayout::transform($layout)->home();
            MyBlogLayout::transform($layout)->home(
                Eloquent::delayed('blog_entry')->take(20)->orderBy('created_at', 'DESC')->all(),
                Eloquent::delayed('blog_comment')->take(5)->orderBy('created_at', 'DESC')->all()
            );
            MyBlogLayout::transform($layout)
                ->baseTemplate()
                ->applyAssets([
                    'concat'=>false,
                ])->updateEtags();
            return AppController::respondLayout($request, $layout);
        };
    }

    public function detail() {
        $layout = $this->layout;
        return function (Request $request, $id) use($layout) {
            $urlFor = $layout->config['helpers']['urlFor'];

            HTMLTransforms::transform($layout)->createBase();
            BlogLayout::transform($layout)->detail();
            MyBlogLayout::transform($layout)->detail(
                Eloquent::delayed('blog_entry')->where('id', '=', $id)->one(),
                Eloquent::delayed('blog_comment')->where('blog_entry_id', '=', $id)->take(5)->orderBy('created_at','DESC')->all(),
                Eloquent::delayed('blog_comment')->where('blog_entry_id', '!=', $id)->take(5)->orderBy('created_at','DESC')->all()
            );
            jQueryTransforms::transform($layout)->inject('page_footer_js');
            jQueryTransforms::transform($layout)->ajaxify('blog_detail_comments', [
                'isAjax'=> $request->isXmlHttpRequest(),
                'url'   => $urlFor($request->get('_route'), $request->get('_route_params'))
            ]);
            MyBlogLayout::transform($layout)
                ->baseTemplate()
                ->applyAssets([
                    'concat'=>false,
                ])->updateEtags();
            return AppController::respondLayout($request, $layout);
        };
    }
}