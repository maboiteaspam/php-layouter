<?php

namespace MyBlog;


use C\Blog\Transforms as BlogLayout;

use C\ModernApp\jQuery\Transforms as jQueryTransforms;
use C\ModernApp\Dashboard\Transforms as Dashboard;
use C\ModernApp\HTML\Transforms as HTML;

class Transforms extends BlogLayout{

    /**
     * @var jQueryTransforms
     */
    public $jquery;
    /**
     * @var Dashboard
     */
    public $dashboard;
    /**
     * @var HTML
     */
    public $html;

    public function setjQuery ( jQueryTransforms $jQuery) {
        $this->jquery = $jQuery;
    }
    public function setDashboard ( Dashboard $dashboard) {
        $this->dashboard = $dashboard;
    }
    public function setHTML ( HTML $T) {
        $this->html = $T;
    }

    public function baseTemplate ($fromClass=__CLASS__) {

        $this->then(
            $this->html->baseTemplate()
        )->setTemplate('body_top',
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
        ])->insertAfterBlock('body_footer', 'extra_footer', [
            'body'=>'some'
        ])->then(
            $this->dashboard
            ? $this->dashboard->show($fromClass)
            : null
        )->then(
            $this->jquery->inject()
        );
        return $this;
    }

    public function home ($entries, $latestComments) {

        $this->then(
            parent::home()
        )->updateBlock('body_content',
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

        $this->then(
            parent::detail()
        )->updateData('body_content', [
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