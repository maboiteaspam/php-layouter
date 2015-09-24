<?php
namespace C\Layout;

use C\Repository\RepositoryProxy;
use C\TagableResource\TagedResource;
use C\TagableResource\TagableResourceInterface;
use C\View\Context;

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
        if ($what==='all') {
            $this->body = "";
            $this->data = [];
            $this->assets = [];
            $this->options = [
                'template'=>''
            ];
        } else {
            if (strpos($what, "data")) {
                $this->data = [];
            }
            if (strpos($what, "options")) {
                $this->options = ["template"=>""];
            }
            if (strpos($what, "assets")) {
                $this->assets = [];
            }
        }
    }

    public function resolve (Context $context){
        $block = $this;
        if ($block
            && !$block->resolved
            && isset($block->options['template'])
            && $block->options['template']) {

            $fn = $block->options['template'];
            if(!is_callable($block->options['template'])) {
                $fn = function (Block $block) {
                    $block->resolved = true; // this will help to prevent recursive call when set above.
                    ob_start();
                    extract($block->unwrapData(['block']), EXTR_SKIP);
                    require($block->options['template']);
                    $block->body = ob_get_clean();
                };
            }

            if ($fn) {
                $context->setBlockToRender($this);
                $boundFn = \Closure::bind($fn, $context);
                $boundFn($block);
            } else {
                // weird stuff in template.
            }
        }
    }



    public function getTaggedResource (){
        $res = new TagedResource();

        $res->addResource($this->id);
        if (isset($this->options['template'])) {
            $template = $this->options['template'];
            if ($template) {
                $res->addResource($template, 'file');
            }
        }
        foreach($this->assets as $target=>$assets) {
            foreach($assets as $i=>$asset){
                if ($asset) {
                    $res->addResource($target);
                    $res->addResource($i);
                    $res->addResource($asset, 'file');
                }
            }
        }

        foreach($this->data as $name => $data){
            if ($data instanceof TagableResourceInterface) {
                $res->addTaggedResource($data->getTaggedResource());
            } else {
                $res->addResource($data);
            }
        }
        return $res;
    }

    public function unwrapData ($notNames=[]) {
        $unwrapped = [];
        foreach($this->data as $name => $data){
            if (!in_array($name, $notNames)) {
                if ($data instanceof RepositoryProxy) {
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