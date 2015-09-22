<?php
namespace C\jQueryLayoutBuilder;

use C\HTMLLayoutBuilder\Transforms as HTMLTransforms;

class Transforms extends HTMLTransforms{

    /**
     * @param mixed $app
     * @return Transforms
     */
    public static function transform($app) {
        return new Transforms($app);
    }

    public function inject($options=[]){
        $options = array_merge([
            'jquery'=>__DIR__ . '/assets/jquery-2.1.3.min.js',
            'target'=>'page_footer_js',
        ], $options);
        $this->updateAssets('body', [
            $options['target']=>[$options['jquery']],
        ], true);
        return $this;
    }

    public function tooltipster($options=[]){
        $options = array_merge([
            'js'=>__DIR__ . '/assets/tooltipster-master/js/jquery.tooltipster.min.js',
            'css'=>__DIR__ . '/assets/tooltipster-master/css/tooltipster.css',
            'theme'=>__DIR__ . '/assets/tooltipster-master/css/themes/tooltipster-shadow.css',
            'css_target'=>'page_head_css',
            'js_target'=>'page_footer_js',
        ], $options);
        $this->updateAssets('body', [
            $options['css_target']=>[$options['css'], $options['theme']],
            $options['js_target']=>[$options['js']],
        ]);
        return $this;
    }

    public function ajaxify($target, $options=[]){
        $options = array_merge(['url'=>'', 'isAjax'=>false], $options);

            if (!$options['isAjax']) {
                $id = sha1($target.$options['url']);

                $this->setTemplate($target,
                    ''
                )->setBody($target,
                    '<div id="'.$id.'"></div>'
                )->setTemplate($target.'_ajax',
                    __DIR__.'/templates/ajaxified-block.php'
                )->updateData($target.'_ajax', [
                    'url'   => $options['url'],
                    'id'    => $id,
                    'target'=> $target,
                ]);

                $this->insertAfter('page_footer_js', $target.'_ajax', []);
            } else {
                $this->layout->block = $target;
            }
        return $this;
    }
}
