<?php

namespace MyBlog;

use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;

use C\DebugLayoutBuilder\Transforms as debugTransforms;
use MyBlog\Transforms as MyBlogLayout;
use \C\Blog\CommentForm as MyCommentForm;
use \C\BlogData\PO\Entry as Entry;
use \C\BlogData\PO\Comment as Comment;
use \C\Data\LazyCapsule as Lazy;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

class Controller{

    public function home() {
        return function (Application $app) {
            $entryModel = new Entry();
            $commentModel = new Comment();
            MyBlogLayout::transform($app)
                ->baseTemplate(__CLASS__)
                ->home(
                    Lazy::autoTagged($entryModel->mostRecent())->get(),
                    Lazy::autoTagged($commentModel->mostRecent())->get()
                )->finalize();
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

            $entryModel = new Entry();
            $commentModel = new Comment();

            MyBlogLayout::transform($app)
                ->baseTemplate(__CLASS__)
                ->detail(
                    Lazy::autoTagged($entryModel->byId($id))->first(),
                    Lazy::autoTagged($commentModel->byEntryId($id))->get(),
                    Lazy::autoTagged($commentModel->mostRecent())->where('blog_entry_id', '!=', $id)->get()
                )->updateData('blog_form_comments', [
                    'form' => $form,
                ])->then(
                    jQueryTransforms::transform($app)->ajaxify('blog_detail_comments', [
                        'isAjax'=> $request->isXmlHttpRequest(),
                        'url'   => $urlFor($request->get('_route'), $request->get('_route_params'))
                    ])
                )->finalize();
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
                $commentModel = new Comment();
                $commentModel->insert($data);
                return $app->json($data);
            }
            $form->getErrors();
            return $app->json($form->getErrors(), 500);
        };
    }
}