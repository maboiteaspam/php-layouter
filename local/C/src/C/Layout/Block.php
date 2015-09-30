<?php
namespace C\Layout;

use C\TagableResource\TagedResource;
use C\TagableResource\TagableResourceInterface;
use C\TagableResource\UnwrapableResourceInterface;
use C\View\Context;
use C\FS\KnownFs;

class Block implements TagableResourceInterface{

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

    // this are runtime data to help debug and so on.
    public $stack = [];
    public $displayed_block = [
        /* [array,of,block,id,displayed]*/
    ];


    public function __construct($id) {
        $this->id = $id;
    }

    public function clear ($what='all') {
        if ($what==='all' || $what==='') {
            $this->body = "";
            $this->data = [];
            $this->assets = [];
            $this->options = [
                'template' => ''
            ];
        } else {
            if (strpos($what, "data")) {
                $this->data = [];
            }
            if (strpos($what, "options")) {
                $this->options = ["template" => ""];
            }
            if (strpos($what, "assets")) {
                $this->assets = [];
            }
        }
    }

    public function resolve (KnownFs $fs, Context $context){
        if (!$this->resolved) {
            $this->resolved = true;
            if (isset($this->options['template'])
                && $this->options['template']) {

                $fn = $this->options['template'];
                if(!is_callable($this->options['template'])) {
                    $fn = function (Block $block) use($fs) {
                        ob_start();
                        extract($block->unwrapData(['block']), EXTR_SKIP);
                        $template = $fs->get($block->options['template']);
                        if ($template!==false) require ($template['absolute_path']);
                        else require ($block->options['template']);
                        $block->body = ob_get_clean();
                    };
                }

                if ($fn) {
                    $context->setBlockToRender($this);
                    $boundFn = \Closure::bind($fn, $context);
                    $boundFn($this);
                } else {
                    // weird stuff in template.
                }
            }
        }
    }


    /**
     * @return TagedResource
     * @throws \Exception
     */
    public function getTaggedResource (){
        $res = new TagedResource();

        if ($this->resolved) {
            $res->addResource($this->id);
            if (isset($this->options['template'])) {
                $template = $this->options['template'];
                if ($template) {
                    $res->addResource($template, 'template');
                }
            }
            foreach($this->assets as $target=>$assets) {
                foreach($assets as $i=>$asset){
                    if ($asset) {
                        $res->addResource($target);
                        $res->addResource($i);
                        $res->addResource($asset, 'asset');
                    }
                }
            }

            foreach($this->data as $name => $data){
                if ($data instanceof TagableResourceInterface) {
                    $res->addTaggedResource($data->getTaggedResource(), $name);
                } else {
                    $res->addResource($data, 'po', $name);
                }
            }
        }

        return $res;
    }

    public function unwrapData ($notNames=[]) {
        $unwrapped = [];
        foreach($this->data as $name => $data){
            if (!in_array($name, $notNames)) {
                if ($data instanceof UnwrapableResourceInterface) {
                    $unwrapped[$name] = $data->unwrap();
                } else {
                    $unwrapped[$name] = $data;
                }
            } else {
                throw new \Exception("Forbidden data name '$name' is forbidden and can t be overwritten");
            }
        }
        return $unwrapped;
    }
}
