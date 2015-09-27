<?php
namespace C\Blog;

use C\Blog\Transforms as BlogLayout;
use Silex\Application;

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
        return function (Application $app) {
            /* @var $entryRepo \C\BlogData\EntryRepositoryInterface as EntryRepo */
            $entryRepo = $app[$this->entryRepo];
            BlogLayout::transform($app['layout'])
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entries' => $entryRepo->mostRecent()
                ]);
            return $app['layout']->render();
        };
    }

    public function entryDetail() {
        return function (Application $app, $id) {
            /* @var $entryRepo \C\BlogData\EntryRepositoryInterface as EntryRepo */
            $entryRepo = $app[$this->entryRepo];
            BlogLayout::transform($app['layout'])
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entry' => $entryRepo->byId($id)
                ]);
            return $app['layout']->render();
        };
    }

    public function entryComments() {
        return function (Application $app) {
            /* @var $commentRepo \C\BlogData\CommentRepositoryInterface as EntryRepo */
            $commentRepo = $app[$this->commentRepo];
            BlogLayout::transform($app['layout'])
                ->setTemplate('root', __DIR__.'/templates/entry-comments.php')
                ->setTemplate('root', [
                    'comments' => $commentRepo->mostRecent()
                ]);
            return $app['layout']->render();
        };
    }

}