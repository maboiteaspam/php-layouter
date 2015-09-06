<?php

namespace MyBlog;

use C\ProductionLine\Pipeline;
use C\LayoutBuilder\Transforms as BaseTransforms;
use C\HTMLLayoutBuilder\Transforms as HTMLTransforms;

class Transforms{
    public static function baseTemplate () {
        $stream = Pipeline::passThrough(BaseTransforms::set('body_top',[
            'options'=>[
                'template'=> __DIR__ . '/templates/top.php',
            ],
            'data'=>[
                'logo'=> '',
            ],
        ]));
        $stream->pipe(BaseTransforms::updateAssets('body', [
            'template_head_css'=>[
                __DIR__ . '/assets/blog.css',
                __DIR__ . '/assets/template.css'
            ],
            'page_footer_js'=>[
                __DIR__ . '/assets/index.js'
            ],
        ]));
        $stream->pipe(BaseTransforms::insertAfter('body_footer', 'extra_footer', [
            'body'=>'some'
        ]));
        $stream->pipe(HTMLTransforms::applyAssets());
        $stream->pipe(BaseTransforms::updateEtags());

        return $stream;
    }

    public static function home ($entries, $latestComments) {
        $stream = Pipeline::passThrough(BaseTransforms::updateBlock('body_content_right',
            ['from'      => 'home_rb'],
            ['comments'  => $latestComments],
            ['template'  => __DIR__ . '/templates/right-bar.php']
        ));
        $stream->pipe(BaseTransforms::updateBlock('body_content',
            ['from'      => 'home'],
            ['entries'   => $entries]
        ));

        return $stream;
    }

    public static function detail ($entry, $comments, $latestComments) {
        $stream = Pipeline::passThrough(BaseTransforms::updateBlock('body_content_right',
            ['from'      => 'blog_rb'],
            ['comments'  => $latestComments],
            ['template'  => __DIR__ . '/templates/right-bar.php']
        ));
        $stream->pipe(BaseTransforms::updateBlock('body_content',
            ['from'      => 'blog_detail'],
            ['entry'     => $entry]
        ));
        $stream->pipe(BaseTransforms::updateBlock('blog_detail_comments',
            ['from'      => 'blog_detail_comment'],
            ['comments'  => $comments]
        ));

        return $stream;
    }

}