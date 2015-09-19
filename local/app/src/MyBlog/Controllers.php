<?php

namespace MyBlog;

use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;

use MyBlog\Transforms as MyBlogLayout;
use \C\Blog\CommentForm as MyCommentForm;
use \C\BlogData\Eloquent\Entry as Entry;
use \C\BlogData\Eloquent\Comment as Comment;
use \C\Data\Eloquent;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

class Controllers{

    public function home() {
        return function (Application $app) {
            $entryModel = new Entry();
            $commentModel = new Comment();
            MyBlogLayout::transform($app)
                ->baseTemplate(__CLASS__)
                ->home(
                    Eloquent::wrap($entryModel->mostRecent())->get(),
                    Eloquent::wrap($commentModel->mostRecent())->get()
                )->finalize();
            $response = new Response();
            return $app['layout.responder']($response);
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

            $entryModel = new Entry();
            $commentModel = new Comment();

            MyBlogLayout::transform($app)
                ->baseTemplate(__CLASS__)
                ->detail(
                    Eloquent::wrap($entryModel->byId($id))->first(),
                    Eloquent::wrap($commentModel->byEntryId($id))->get(),
                    Eloquent::wrap($commentModel->mostRecent())->where('blog_entry_id', '!=', $id)->get()
                )->updateData('blog_form_comments', [
                    'form' => $form,
                ])->then(
                    jQueryTransforms::transform($app)->ajaxify('blog_detail_comments', [
                        'isAjax'=> $request->isXmlHttpRequest(),
                        'url'   => $urlFor($request->get('_route'), $request->get('_route_params'))
                    ])
                )->finalize();

            $response = new Response();
            return $app['layout.responder']($response);
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
                $commentModel = new Comment();
                $commentModel->insert($data);
                return $app->json($data);
            }
            $form->getErrors();
            return $app->json($form->getErrors(), 500);
        };
    }
}