<?php
namespace C\jQueryLayoutBuilder;

use C\HTMLLayoutBuilder\Transforms as HTMLTransforms;
use C\LayoutBuilder\Layout\Layout;

class Transforms extends HTMLTransforms{

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout) {
        return new Transforms($layout);
    }

    public function inject($target, $options=[]){
        $options = array_merge([
            'jquery'=>__DIR__ . '/assets/jquery-2.1.3.min.js'
        ], $options);
        $this->updateAssets('body', [
            $target=>[$options['jquery']],
        ], true);
        return $this;
    }

    public function ajaxify($target, $options=[]){
        $options = array_merge(['url'=>'', 'isAjax'=>false], $options);

            if (!$options['isAjax']) {
                $id = sha1($target.$options['url']);
                $this->setTemplate($target, '');
                $this->setBody($target, '<div id="'.$id.'"></div>');
                $this->setTemplate($target.'_ajax', __DIR__.'/templates/ajaxified-block.php');
                $this->updateData($target.'_ajax', [
                    'url'   => $options['url'],
                    'id'    => $id,
                ]);
                $layout = $this->layout;
                $this->layout->on('after_render_page_footer_js', function () use($layout, $target) {
                    $layout->displayBlock($target.'_ajax');
                });
            } else {
                $this->layout->block = $target;
            }
        return $this;
    }
}
