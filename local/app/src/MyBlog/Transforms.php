<?php

namespace MyBlog;


use C\Blog\Transforms as BlogLayout;

use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;
use C\DebugLayoutBuilder\Transforms as debugTransforms;
use C\Dashboard\Transforms as Dashboard;

class Transforms extends BlogLayout{

    /**
     * @param mixed $app
     * @return Transforms
     */
    public static function transform ($app) {
        return new Transforms($app);
    }

    public function baseTemplate ($fromClass=__CLASS__) {
        parent::baseTemplate();
        $this->setTemplate('body_top',
            __DIR__.'/templates/top.php'
        )->updateData('body_top', [
            'logo'=> '',
        ])->updateAssets('body', [
            'template_head_css'=>[
                __DIR__ . '/assets/blog.css',
                __DIR__ . '/assets/template.css'
            ],
            'page_footer_js'=>[
                __DIR__ . '/assets/index.js'
            ],
        ])->insertAfter('body_footer', 'extra_footer', [
            'body'=>'some'
        ])->then(
            Dashboard::transform($this->app)->show(true, $fromClass)
        )->then(
            jQueryTransforms::transform($this->app)->inject()
        );
        return $this;
    }

    public function home ($entries, $latestComments) {
        parent::home();
        $this->updateBlock('body_content',
            ['from'      => 'home'],
            ['entries'   => $entries]
        )->updateBlock('body_content_right',
            ['from'      => 'home_rb'],
            ['comments'  => $latestComments],
            ['template'  => __DIR__ . '/templates/right-bar.php']
        );
        return $this;
    }

    public function detail ($entry, $comments, $latestComments) {
        parent::detail();
        $this->updateData('body_content', [
            'entry'  => $entry,
        ])->updateMeta('body_content', [
            'from'      => 'blog_detail',
        ]);

        $this->updateData('blog_detail_comments', [
            'comments'  => $comments,
        ])->updateMeta('blog_detail_comments', [
            'from'      => 'blog_detail_comments',
        ]);

        $this->setTemplate('body_content_right',
            __DIR__.'/templates/right-bar.php'
        )->updateData('body_content_right', [
            'comments'  => $latestComments,
        ])->updateMeta('body_content_right', [
            'from'      => 'blog_rb',
        ]);

        return $this;
    }

}