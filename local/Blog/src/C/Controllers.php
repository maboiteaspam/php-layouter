<?php
namespace C\Blog;

use C\BlogData\CommentRepositoryInterface as CommentRepo;
use C\BlogData\EntryRepositoryInterface as EntryRepo;
use C\Blog\Transforms as BlogLayout;

class Controllers {

    public $entryRepo;
    public $commentRepo;

    public function __construct(EntryRepo $entryRepo, CommentRepo $commentRepo) {
        $this->entryRepo = $entryRepo;
        $this->commentRepo = $commentRepo;
    }

    public function entryList($app) {
        return function () use($app) {
            BlogLayout::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entries' => $this->entryRepo->mostRecent()
                ]);
            return $app['layout']->render();
        };
    }

    public function entryDetail($app) {
        return function ($id) use($app) {
            BlogLayout::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entry' => $this->entryRepo->byId($id)
                ]);
            return $app['layout']->render();
        };
    }

    public function entryComments($app) {
        return function () use($app) {
            BlogLayout::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-comments.php')
                ->setTemplate('root', [
                    'comments' => $this->commentRepo->mostRecent()
                ]);
            return $app['layout']->render();
        };
    }

}