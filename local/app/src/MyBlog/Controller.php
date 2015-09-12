<?php

namespace MyBlog;

use C\Data\Eloquent;

use C\Foundation\AppController;

use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;

use C\DebugLayoutBuilder\Transforms as debugTransforms;
use MyBlog\Transforms as MyBlogLayout;
use \C\Blog\CommentForm as MyCommentForm;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

class Controller{

    public function home() {
        return function (Application $app) {
            MyBlogLayout::transform($app)
                ->baseTemplate()
                ->home(
                    Eloquent::delayed('blog_entry')->take(20)->orderBy('created_at', 'DESC')->all(),
                    Eloquent::delayed('blog_comment')->take(5)->orderBy('created_at', 'DESC')->all()
                )->then(
                    debugTransforms::transform($app)->debug(__CLASS__)
                )
                ->finalize();
            return $app['layout_responder']();
        };
    }

    public function detail($postCommentUrl) {
        return function (Application $app, Request $request, $id) use($postCommentUrl) {
            $urlFor = $app['layout']->config['helpers']['urlFor'];

            $comment = new MyCommentForm();
            /* @var $form \Symfony\Component\Form\Form*/
            $form = $app['form.factory']->createBuilder('form', $comment)
                ->setAction($urlFor($postCommentUrl, ['id'=>$id]))
                ->setMethod('POST')
                ->getForm();

            $form->handleRequest($request);

            MyBlogLayout::transform($app)
                ->baseTemplate()
                ->detail(
                    Eloquent::delayed('blog_entry')->where('id', '=', $id)->one(),
                    Eloquent::delayed('blog_comment')->where('blog_entry_id', '=', $id)->take(5)->orderBy('created_at','DESC')->all(),
                    Eloquent::delayed('blog_comment')->where('blog_entry_id', '!=', $id)->take(5)->orderBy('created_at','DESC')->all()
                )->updateData('blog_form_comments', [
                    'form'=> $form,
                ])->then(
                    jQueryTransforms::transform($app)->ajaxify('blog_detail_comments', [
                        'isAjax'=> $request->isXmlHttpRequest(),
                        'url'   => $urlFor($request->get('_route'), $request->get('_route_params'))
                    ])
                )->then(
                    debugTransforms::transform($app)->debug(__CLASS__)
                )
                ->finalize();
            return $app['layout_responder']();
        };
    }

    public function postComment() {
        return function (Application $app, Request $request, $id) {
            $comment = new MyCommentForm();
            $form = $app['form.factory']->createBuilder('form', $comment)->getForm();

            /* @var $form \Symfony\Component\Form\Form*/
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $data['blog_entry_id'] = $id;
                $data['id'] = Eloquent::table('blog_comment')->insertGetId($data);
                return $app->json($data);
            }
            $form->getErrors();
            return $app->json($form->getErrors(), 500);
        };
    }
}