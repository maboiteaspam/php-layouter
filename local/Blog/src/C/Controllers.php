<?php
namespace C\Blog;

use C\BlogData\CommentRepositoryInterface as CommentRepo;
use C\BlogData\EntryRepositoryInterface as EntryRepo;
use C\Blog\Transforms as BlogLayout;

class Controllers {

    public $entryRepo;
    public $commentRepo;

    /**
     * @var BlogLayout
     */
    public $blog;

    public function __construct(EntryRepo $entryRepo, CommentRepo $commentRepo) {
        $this->entryRepo = $entryRepo;
        $this->commentRepo = $commentRepo;
    }

    public function setBlogTransforms ( BlogLayout $T) {
        $this->blog = $T;
    }

    public function entryList() {
        return function () {
            $this->blog
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entries' => $this->entryRepo->mostRecent()
                ]);
            return $this->blog->layout->render();
        };
    }

    public function entryDetail() {
        return function ($id) {
            $this->blog
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entry' => $this->entryRepo->byId($id)
                ]);
            return $this->blog->layout->render();
        };
    }

    public function entryComments() {
        return function () {
            $this->blog
                ->setTemplate('root', __DIR__.'/templates/entry-comments.php')
                ->setTemplate('root', [
                    'comments' => $this->commentRepo->mostRecent()
                ]);
            return $this->blog->layout->render();
        };
    }

}