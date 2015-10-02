<?php
namespace MyBlog;

use C\Layout\TransformsInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use C\ModernApp\File\Transforms as FileLayout;
use C\ModernApp\jQuery\Transforms as jQuery;
use C\ModernApp\HTML\Transforms as HTML;

use MyBlog\Transforms as MyBlogLayout;
use \C\Blog\CommentForm as MyCommentForm;
use \C\Form\FormBuilder;

class Controllers{

    /**
     * name of the repo
     * @var string
     */
    public $entryRepo;

    /**
     * name of the repo
     * @var string
     */
    public $commentRepo;

    public function __construct($entryRepo, $commentRepo) {
        $this->entryRepo = $entryRepo;
        $this->commentRepo = $commentRepo;
    }

    public function home() {
        return function (Application $app) {
            /* @var $entryRepo \C\BlogData\EntryRepositoryInterface */
            $entryRepo = $app[$this->entryRepo];
            /* @var $commentRepo \C\BlogData\CommentRepositoryInterface */
            $commentRepo = $app[$this->commentRepo];
            /* @var $requestData \C\HTTP\RequestProxy */
            $requestData = $app['httpcache.request'];
            $listEntryBy = 5;
            MyBlogLayout::transform($app['layout'])
                ->forDevice('desktop')
                ->baseTemplate(__CLASS__)
                ->home(
                    $entryRepo
                        ->tagable( $entryRepo->tager()->lastUpdateDate() )
                        ->mostRecent($requestData->get('page'), $listEntryBy),
                    $commentRepo
                        ->tagable( $commentRepo->tager()->lastUpdateDate() )
                        ->mostRecent(),
                    $entryRepo->tagable()->countAll(),
                    $listEntryBy
//                )->then(
//                    FileLayout::transform($app['layout'])->loadFile( "test_layout.yml" )
                )
                ->forDevice('mobile')
                ->baseTemplate(__CLASS__)
                ->setBody('body_content', 'Hello, this mobile layout !!');

            $response = new Response();
            return $app['layout.responder']($response);
        };
    }

    public function detail($postCommentUrl) {
        return function (Application $app, Request $request, $id) use($postCommentUrl) {
            /* @var $entryRepo \C\BlogData\EntryRepositoryInterface */
            $entryRepo = $app[$this->entryRepo];
            /* @var $commentRepo \C\BlogData\CommentRepositoryInterface */
            $commentRepo = $app[$this->commentRepo];
            /* @var $generator \Symfony\Component\Routing\Generator\UrlGenerator */
            $generator = $app["url_generator"];

            $commentForm = new MyCommentForm();

            /* @var $form \Symfony\Component\Form\Form */
            $form = $app['form.factory']
                ->createBuilder($commentForm, ["email"=>"some"])
                ->setAction($generator->generate($postCommentUrl, ['id'=>$id]))
                ->setMethod('POST')
                ->getForm();

            $form->handleRequest($request);

            MyBlogLayout::transform($app['layout'])
                ->forDevice('desktop')
                ->baseTemplate(__CLASS__)
                ->detail(
                    $entryRepo
                        ->tagable( $entryRepo->tager()->byId($id) )
                        ->byId($id),
                    $commentRepo
                        ->tagable( $commentRepo->tager()->lastUpdatedByEntryId($id) )
                        ->byEntryId($id),
                    $commentRepo
                        ->tagable( $commentRepo->tager()->mostRecent([$id]) )
                        ->mostRecent([$id])
                )->then(function (MyBlogLayout $transform) use($request, $generator) {
                    jQuery::transform($transform->getLayout())->ajaxify('blog_detail_comments', [
                        'url'   => $generator->generate($request->get('_route'), $request->get('_route_params'))
                    ]);
                })->then(function (MyBlogLayout $transform) use($form, $request, $generator) {
                    jQuery::transform($transform->getLayout())->ajaxify('blog_form_comments', [
                        'url'   => $generator->generate($request->get('_route'), $request->get('_route_params'))
                    ])->updateData('blog_form_comments', [
                        'form' => FormBuilder::createView($form),
                    ]);
                })
                ->forDevice('mobile')
                ->baseTemplate(__CLASS__);

            $response = new Response();
            return $app['layout.responder']($response);
        };
    }

    public function postComment() {
        return function (Application $app, Request $request, $id) {
            $comment = new MyCommentForm();
            $form = $app['form.factory']
                ->createBuilder('form', $comment)
                ->getForm();

            /* @var $form \Symfony\Component\Form\Form*/
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $data['blog_entry_id'] = $id;
                /* @var $commentRepo \C\BlogData\CommentRepositoryInterface */
                $commentRepo = $app[$this->commentRepo];
                $commentRepo->insert($data);
                return $app->json($data);
            }

            $form->getErrors();
            return $app->json($form->getErrors(), 500);
        };
    }
}