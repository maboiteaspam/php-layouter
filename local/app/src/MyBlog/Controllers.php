<?php
namespace MyBlog;

use C\HTTP\RequestProxy;
use C\Layout\TransformsInterface;
use Silex\Application;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use C\ModernApp\File\Transforms as FileLayout;
use C\Esi\Transforms as PunchHole;
use C\ModernApp\jQuery\Transforms as jQuery;

use C\Layout\Transforms as Transforms;
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
        return function (Application $app, Request $request) {
            /* @var $entryRepo \C\BlogData\EntryRepositoryInterface */
            $entryRepo = $app[$this->entryRepo];
            /* @var $commentRepo \C\BlogData\CommentRepositoryInterface */
            $commentRepo = $app[$this->commentRepo];
            $response = new Response();

            return FileLayout::transform($app)
                ->importFile("MyBlog:/home.yml")
                ->then(function (TransformsInterface $transform) use ($app, $entryRepo, $commentRepo) {
                    /* @var $requestData \C\HTTP\RequestProxy */
                    $requestData = new RequestProxy($app['request']);
                    $listEntryBy = 5;
                    Transforms::transform($app)
                        ->updateData('body_content',[
                            'entries'   => $entryRepo
                                ->tagable( $entryRepo->tager()->lastUpdateDate() )
                                ->mostRecent($requestData->get('page'), $listEntryBy)
                        ])
                        ->updateData('rb_latest_comments',[
                            'comments'  => $commentRepo
                                ->tagable( $commentRepo->tager()->lastUpdateDate() )
                                ->mostRecent()

                        ])
                        ->updateData('blog-entries-pagination', [
                            'count'         => $entryRepo->tagable()->countAll(),
                            'by'            => $listEntryBy,
                        ]);

                })->respond($request, $response)
            ;
        };
    }

    public function detail($postCommentUrl) {
        return function (Application $app, Request $request, $id) use($postCommentUrl) {
            /* @var $entryRepo \C\BlogData\EntryRepositoryInterface */
            $entryRepo = $app[$this->entryRepo];
            /* @var $commentRepo \C\BlogData\CommentRepositoryInterface */
            $commentRepo = $app[$this->commentRepo];
            $response = new Response();

            return FileLayout::transform($app)
                ->importFile("MyBlog:/detail.yml")
                ->forDevice('desktop')

                ->then(function (TransformsInterface $transform) use ($app, $id, $entryRepo, $commentRepo) {
                    Transforms::transform($app)
                        ->updateData('body_content',[
                            'entry' => $entryRepo
                                    ->tagable( $entryRepo->tager()->byId($id) )
                                    ->byId($id)
                        ])
                        ->updateData('blog_detail_comments',[
                            'comments'  => $commentRepo
                                ->tagable( $commentRepo->tager()->lastUpdatedByEntryId($id) )
                                ->byEntryId($id)

                        ])
                        ->updateData('rb_latest_comments', [
                            'comments'  => $commentRepo
                                ->tagable( $commentRepo->tager()->mostRecent([$id]) )
                                ->mostRecent([$id]),
                        ]);

                })->then(function (TransformsInterface $transform) use($app, $request) {
                    /* @var $generator \Symfony\Component\Routing\Generator\UrlGenerator */
                    $generator = $app["url_generator"];

                    PunchHole::transform($app)
                        ->esify('blog_detail_comments', [
                            'url'   => $generator->generate($request->get('_route'), $request->get('_route_params')),
                        ]);

                })->then(function (TransformsInterface $transform) use($app, $request, $postCommentUrl, $id) {
                    /* @var $generator \Symfony\Component\Routing\Generator\UrlGenerator */
                    $generator = $app["url_generator"];

                    $commentForm = new MyCommentForm();

                    /* @var $form \Symfony\Component\Form\Form */
                    $form = $app['form.factory']
                        ->createBuilder($commentForm)
                        ->setAction($generator->generate($postCommentUrl, ['id'=>$id]))
                        ->setMethod('POST')
                        ->getForm();

                    $form->handleRequest($request);

                    jQuery::transform($app)
                        ->ajaxify('blog_form_comments', [
                            'url'   => $generator->generate($request->get('_route'), $request->get('_route_params')),
                        ])->updateData('blog_form_comments', [
                            'form' => FormBuilder::createView($form),
                        ]);

                })->respond($request, $response)
            ;
        };
    }

    public function postComment() {
        return function (Application $app, Request $request, $id) {
            $comment = new MyCommentForm();
            $form = $app['form.factory']
                ->createBuilder($comment)
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

//            $errors = $app['validator']->validate($form);
//            dump($form->getData());
//            dump($form->isValid());
//            dump($form->getErrors()->getForm());
//            dump(getFormErrors($form));
//            dump($comment);

            return $app->json(getFormErrors($form), 500);
        };
    }
}

function getFormErrors(Form $form)
{
    $errors = array();

    // Global
    foreach ($form->getErrors() as $error) {
        $errors[$form->getName()][] = $error->getMessage();
    }

    // Fields
    foreach ($form as $child /** @var Form $child */) {
        if (!$child->isValid()) {
            foreach ($child->getErrors() as $error) {
                $errors[$child->getName()][] = $error->getMessage();
            }
        }
    }

    return $errors;
}
