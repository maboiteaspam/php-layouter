<?php

namespace C\LayoutBuilder\Layout;

use C\Data\TaggedData;

class Block{

    public $id;
    public $body;
    public $resolved = false;

    public $options = [
    ];
    public $data = [];
    public $assets = [
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
                    foreach($block->data as $name => $data){
                        if ($name!='block') {
                            if ($data instanceof TaggedData) {
                                $$name = $data->get();
                            } else {
                                $$name = $data;
                            }
                        } else {
                            throw new \Exception('Forbidden data name "block" called in block');
                        }
                    }
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
}
