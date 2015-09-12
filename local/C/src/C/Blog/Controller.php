<?php

namespace C\Blog;

use MyBlog\Transforms as BlogLayout;

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
    foreach (getEntries () as $entry) {
        foreach ($entry['comments'] as $comment) {
            $comments[] = $comment;
        }
    }
    return $comments;
}


class Controller{

    public function entryList($app) {
        return function () use($app) {
            BlogLayout::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entries'=>getEntries()
                ]);
            return $app['layout']->render();
        };
    }

    public function entryDetail($app) {
        return function () use($app) {
            BlogLayout::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-list.php')
                ->setTemplate('root', [
                    'entry'=>getEntries()[0]
                ]);
            return $app['layout']->render();
        };
    }

    public function entryComments($app) {
        return function () use($app) {
            BlogLayout::transform($app)
                ->setTemplate('root', __DIR__.'/templates/entry-comments.php')
                ->setTemplate('root', [
                    'comments'=>getComments()
                ]);
            return $app['layout']->render();
        };
    }

}