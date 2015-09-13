<?php

namespace C\LayoutBuilder\Layout;

use \C\Data\ViewData;

class Block{

    public $id;
    public $body;
    public $resolved = false;

    public $options = [
    ];
    public $data = [];
    public $assets = [
    ];
    public $displayed_block = [
        /* [array,of,block,id,displayed]*/
    ];
    public $meta = [
        'from' => false,
        'etag' => '',
    ];
    public $stack = [];


    public function __construct($id) {
        $this->id = $id;
    }

    public function resolve ($helpers){
        $block = $this;
        if ($block && !$block->resolved && isset($block->options['template']) && $block->options['template']) {
            $fn = $block->options['template'];
            if(!is_callable($block->options['template'])) {
                $fn = function ($helpers, $block) {
                    $block->resolved = true; // this will prevent recursive call when set above.
                    ob_start();
                    extract($block->unwrapData(['block']), EXTR_SKIP);
                    if ($helpers) {
                        foreach($helpers as $name => $helper){
                            if ($name!='block') {
                                $$name = $boundFn = $bcl2 = \Closure::bind($helper, $this);
                            } else {
                                throw new \Exception('Forbidden helper name "block" called in block');
                            }
                        }
                    }
                    require($block->options['template']);
                    $block->body = ob_get_clean();
                };
            }

            if ($fn) {
                $boundFn = $bcl2 = \Closure::bind($fn, $block);
                $boundFn($helpers, $block);
            } else {
                // weird stuff in template.
            }
        }
    }

    public function etagData ($templatesFS, $assetsFS){
        $h = '';
        $h .= $this->id . '-';
        if (isset($this->options['template'])) {
            $template = $this->options['template'];
            if ($templatesFS->file_exists($template)) {
                $a = $templatesFS->get($template);
                $h .= $template . '-';
                $h .= $a['sha1'] . '-';
                $h = sha1($h);
            }
        }
        foreach($this->assets as $target=>$assets) {
            foreach($assets as $i=>$asset){
                if ($assetsFS->file_exists($asset)) {
                    $a = $assetsFS->get($asset);
                    $h .= $target . '-';
                    $h .= $i . '-';
                    $h .= $asset . '-';
                    $h .= $a['sha1'] . '-';
                    $h = sha1($h);
                }
            }
        }

        foreach($this->unwrapEtags() as $name => $data){
            $h = sha1($h.$name.$data);
        }

        return $h;
    }

    public function unwrapEtags (){
        $unwrapped = [];
        foreach($this->data as $name => $data){
            try{
                if ($data instanceof ViewData) {
                    $unwrapped[$name] = serialize($data->getEtag());
                } else {
                    $unwrapped[$name] = serialize($data);
                }
            }catch(\Exception $ex) {
                $unwrapped[$name] = "untaggable data $name";
            }
        }
        return $unwrapped;
    }

    public function unwrapData ($notNames=[]) {
        $unwrapped = [];
        foreach($this->data as $name => $data){
            if (!in_array($name, $notNames)) {
                if ($data instanceof ViewData) {
                    $unwrapped[$name] = $data->unwrap();
                } else {
                    $unwrapped[$name] = $data;
                }
            } else {
                throw new \Exception("Forbidden data name '$name'' is forbidden and can t be overwritten");
            }
        }
        return $unwrapped;
    }
}
