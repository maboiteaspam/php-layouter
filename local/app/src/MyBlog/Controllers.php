<?php
namespace MyBlog;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;

use MyBlog\Transforms as MyBlogLayout;
use \C\Blog\CommentForm as MyCommentForm;

use C\BlogData\CommentRepositoryInterface as CommentRepo;
use C\BlogData\EntryRepositoryInterface as EntryRepo;

class Controllers{

    public $entryRepo;
    public $commentRepo;

    public function __construct(EntryRepo $entryRepo, CommentRepo $commentRepo) {
        $this->entryRepo = $entryRepo;
        $this->commentRepo = $commentRepo;
    }

    public function home() {
        return function (Application $app) {
            MyBlogLayout::transform($app)
                ->baseTemplate(__CLASS__)
                ->home(
                    $this->entryRepo->tagable(
                        $this->entryRepo->tager()->lastUpdateDate()
                    )->mostRecent(),
                    $this->commentRepo->tagable(
                        $this->commentRepo->tager()->lastUpdateDate()
                    )->mostRecent()
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

            MyBlogLayout::transform($app)
                ->baseTemplate(__CLASS__)
                ->detail(
                    $this->entryRepo->tagable(
                        $this->entryRepo->tager()->byId($id)
                    )->byId($id),
                    $this->commentRepo->tagable(
                        $this->commentRepo->tager()->lastUpdatedByEntryId($id)
                    )->byEntryId($id),
                    $this->commentRepo->tagable(
                        $this->commentRepo->tager()->mostRecent([$id])
                    )->mostRecent([$id])
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
                $this->commentRepo->insert($data);
                return $app->json($data);
            }
            $form->getErrors();
            return $app->json($form->getErrors(), 500);
        };
    }
}