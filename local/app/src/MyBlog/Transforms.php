<?php

namespace MyBlog;

use C\LayoutBuilder\Layout\Layout;
use C\HTMLLayoutBuilder\Transforms as HTMLTransforms;

class Transforms extends HTMLTransforms{

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout) {
        return new Transforms($layout);
    }

    public function baseTemplate () {
        $this->set('body_top',[
            'options'=>[
                'template'=> __DIR__ . '/templates/top.php',
            ],
            'data'=>[
                'logo'=> '',
            ],
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
        $this->applyAssets();
        $this->updateEtags();
        return $this;
    }

    public function home ($entries, $latestComments) {
        $this->updateBlock('body_content_right',
            ['from'      => 'home_rb'],
            ['comments'  => $latestComments],
            ['template'  => __DIR__ . '/templates/right-bar.php']
        );
        $this->updateBlock('body_content',
            ['from'      => 'home'],
            ['entries'   => $entries]
        );
        return $this;
    }

    public function detail ($entry, $comments, $latestComments) {
        $this->updateBlock('body_content_right',
            ['from'      => 'blog_rb'],
            ['comments'  => $latestComments],
            ['template'  => __DIR__ . '/templates/right-bar.php']
        );
        $this->updateBlock('body_content',
            ['from'      => 'blog_detail'],
            ['entry'     => $entry]
        );
        $this->updateBlock('blog_detail_comments',
            ['from'      => 'blog_detail_comment'],
            ['comments'  => $comments]
        );
        return $this;
    }

}