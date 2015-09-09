<?php

namespace MyBlog;


use C\Blog\Transforms as BlogLayout;

use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;

class Transforms extends BlogLayout{

    /**
     * @param mixed $options
     * @return Transforms
     */
    public static function transform ($options) {
        return new Transforms($options);
    }

    public function baseTemplate () {
        parent::baseTemplate();
        $this->setTemplate('body_top', __DIR__.'/templates/top.php');
        $this->updateData('body_top', [
            'logo'=> '',
        ]);
        $this->updateAssets('body', [
            'template_head_css'=>[
                __DIR__ . '/assets/blog.css',
                __DIR__ . '/assets/template.css'
            ],
            'page_footer_js'=>[
                __DIR__ . '/assets/index.js'
            ],
        ]);
        $this->insertAfter('body_footer', 'extra_footer', [
            'body'=>'some'
        ]);
        jQueryTransforms::transform($this->options)->inject();
        return $this;
    }

    public function home ($entries, $latestComments) {
        parent::home();
        $this->updateBlock('body_content',
            ['from'      => 'home'],
            ['entries'   => $entries]
        );
        $this->updateBlock('body_content_right',
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
        ]);
        $this->updateMeta('body_content', [
            'from'      => 'blog_detail',
        ]);

        $this->updateData('blog_detail_comments', [
            'comments'  => $comments,
        ]);
        $this->updateMeta('blog_detail_comments', [
            'from'      => 'blog_detail_comments',
        ]);

        $this->setTemplate('body_content_right', __DIR__.'/templates/right-bar.php');
        $this->updateData('body_content_right', [
            'comments'  => $latestComments,
        ]);
        $this->updateMeta('body_content_right', [
            'from'      => 'blog_rb',
        ]);

        return $this;
    }

}