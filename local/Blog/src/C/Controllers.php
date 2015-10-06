<?php
namespace C\Blog;

use C\Layout\Transforms;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Controllers {

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

    public function entryList() {
        return function (Application $app, Request $request) {
            /* @var $entryRepo \C\BlogData\EntryRepositoryInterface as EntryRepo */
            $entryRepo = $app[$this->entryRepo];
            return Transforms::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entries' => $entryRepo->mostRecent()
                ])->respond($request);
        };
    }

    public function entryDetail() {
        return function (Application $app, Request $request, $id) {
            /* @var $entryRepo \C\BlogData\EntryRepositoryInterface as EntryRepo */
            $entryRepo = $app[$this->entryRepo];
            return Transforms::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entry' => $entryRepo->byId($id)
                ])->respond($request);
        };
    }

    public function entryComments() {
        return function (Application $app, Request $request) {
            /* @var $commentRepo \C\BlogData\CommentRepositoryInterface as EntryRepo */
            $commentRepo = $app[$this->commentRepo];
            return Transforms::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-comments.php')
                ->setTemplate('root', [
                    'comments' => $commentRepo->mostRecent()
                ])->respond($request);
        };
    }

}