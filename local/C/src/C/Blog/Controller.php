<?php

namespace Blog;

use C\AppController\Silex as AppController;

use MyBlog\Transforms as BlogLayout;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;


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


class Controller{

    public function entryList() {
        return function (Application $app, Request $request) {
            BlogLayout::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entries'=>getEntries()
                ]);
            return AppController::respond($app, $request);
        };
    }

    public function entryDetail() {
        return function (Application $app, Request $request) {
            BlogLayout::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entry'=>getEntries()[0]
                ]);
            return AppController::respond($app, $request);
        };
    }

    public function entryComments() {
        return function (Application $app, Request $request) {
            BlogLayout::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-comments.php')
                ->setTemplate('root', [
                    'comments'=>getComments()
                ]);
            return AppController::respond($app, $request);
        };
    }

}