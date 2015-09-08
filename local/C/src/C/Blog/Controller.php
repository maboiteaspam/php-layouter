<?php

namespace Blog;

use C\AppController\Silex as AppController;

use C\AppController\Controller as BaseController;
use MyBlog\Transforms as BlogLayout;

use Symfony\Component\HttpFoundation\Request;


function getEntries () {
    $fixtureEntries = include(__DIR__ . '/fixtures/blog-entries.php');
    foreach ($fixtureEntries as $entry) {
        foreach ($entry['comments'] as $comment) {
            $comment['blog_entry_id'] = $entry['id'];
        }
    }
    return $fixtureEntries;
}
function getComments () {
    $comments = [];
    foreach (getEntryList () as $entry) {
        foreach ($entry['comments'] as $comment) {
            $comments[] = $comment;
        }
    }
    return $comments;
}


class Controller extends BaseController{

    public function entryList() {
        $layout = $this->layout;
        return function (Request $request) use($layout) {

            BlogLayout::transform($layout)
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entries'=>getEntries()
                ]);

            return AppController::respondLayout($request, $layout);
        };
    }

    public function entryDetail() {
        $layout = $this->layout;
        return function (Request $request) use($layout) {

            BlogLayout::transform($layout)
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entry'=>getEntries()[0]
                ]);

            return AppController::respondLayout($request, $layout);
        };
    }

    public function entryComments() {
        $layout = $this->layout;
        return function (Request $request) use($layout) {

            BlogLayout::transform($layout)
                ->setTemplate('root', __DIR__.'/templates/entry-comments.php')
                ->setTemplate('root', [
                    'comments'=>getComments()
                ]);

            return AppController::respondLayout($request, $layout);
        };
    }

}