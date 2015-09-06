<?php
namespace C\jQueryLayoutBuilder;

use C\LayoutBuilder\Transforms as BaseTransforms;
use C\LayoutBuilder\Layout\Layout;
use C\HTMLLayoutBuilder\Layout\Builder as Builder;

class Transforms{

    public static function inject($target, $options=[]){
        $options = array_merge([
            'jquery'=>__DIR__ . '/assets/jquery-2.1.3.min.js'
        ], $options);
        return BaseTransforms::updateAssets('body', [
            $target=>[$options['jquery']],
        ]);
    }

    public static function ajaxify($target, $options=[]){
        $options = array_merge(['url'=>'', 'isAjax'=>false], $options);

        return function(Layout $layout) use($target, $options){
            if (!$options['isAjax']) {
                $id = sha1($target.$options['url']);
                Builder::set($layout, $target, [
                    'options'=> [
                        'template' => '',
                    ],
                    'body'=> '<div id="'.$id.'"></div>',
                    'data'=> [],
                ]);
                Builder::set($layout, $target.'_ajax', [
                    'options'=> [
                        'template' => __DIR__.'/templates/ajaxified-block.php',
                    ],
                    'data'=> [
                        'url'   => $options['url'],
                        'id'    => $id,
                    ],
                ]);
                $layout->on('after_render_page_footer_js', function () use($layout, $target) {
                    $layout->displayBlock($target.'_ajax');
                });
            } else {
                $layout->block = $target;
            }
        };
    }
}
