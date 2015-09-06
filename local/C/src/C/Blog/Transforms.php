<?php

namespace C\Blog;

use C\HTMLLayoutBuilder\Layout\Builder;
use C\LayoutBuilder\Layout\Layout;
use C\Misc\Utils;

class Transforms{
    static function home ($options=[]) {
        $options = Utils::mergeMultiBlockOptions($options, [
            'body_content'=>[
                'options'=>[
                    'template'=>__DIR__.'/templates/entry-list.php',
                ],
                'data'=>[
                    'entries'=>[
                        [
                            'id'=>0,
                            'date'=> date('Y-m-d H:i:s'),
                            'author'=>'some',
                            'img_alt'=>'some',
                            'title'=>'some',
                            'content'=>'blog entry',
                            'comments'=>[],
                        ],
                        [
                            'id'=>0,
                            'date'=> date('Y-m-d H:i:s'),
                            'author'=>'some',
                            'img_alt'=>'some',
                            'title'=>'some',
                            'content'=>'blog entry',
                            'comments'=>[],
                        ],
                    ],
                ],
            ],
            'body_footer'=>[
                'options'=>[
                    'template'=>__DIR__.'/templates/footer.php',
                ],
                'data'=>[
                    'year'=> date('Y'),
                ],
            ],
        ]);
        return function(Layout $layout) use($options){
            Builder::setMultiple($layout, $options);
        };
    }
    static function detail ($options=[]) {
        $options = Utils::mergeMultiBlockOptions($options, [
            'body_content'=>[
                'options'=>[
                    'template'=>__DIR__.'/templates/entry-detail.php',
                ],
                'data'=>[
                    'entry'=>[
                        'id'=>0,
                        'date'=> date('Y-m-d H:i:s'),
                        'author'=>'some',
                        'img_alt'=>'some',
                        'title'=>'some',
                        'content'=>'blog entry',
                        'comments'=>[],
                    ],
                ],
            ],
            'blog_detail_comments'=>[
                'options'=>[
                    'template'=>__DIR__.'/templates/entry-comments.php',
                ],
                'data'=>[
                    'comments'=>[],
                ],
            ],
            'body_footer'=>[
                'options'=>[
                    'template'=>__DIR__.'/templates/footer.php',
                ],
                'data'=>[
                    'year'=> date('Y'),
                ],
            ],
        ]);
        return function(Layout $layout) use($options){
            Builder::setMultiple($layout, $options);
        };
    }
}
