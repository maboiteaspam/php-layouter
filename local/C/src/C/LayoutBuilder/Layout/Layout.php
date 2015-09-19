<?php

namespace C\LayoutBuilder\Layout;

use C\TagableResource\TagedResource;
use C\TagableResource\TagableResourceInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;
use C\Misc\Utils;

class Layout implements TagableResourceInterface{

    /**
     * id of the block to start display from
     *
     * @var string
     */
    public $block;
    /**
     * @var RegistryBlock
     */
    public $registry;
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    public $dispatcher;

    public $defaultOptions = [
        'options'=>[]
    ];

    /**
     * @var string
     */
    private $currentBlockInRender;

    public function __construct ($config=[]) {
        $this->registry = new RegistryBlock();

        $this->block = 'root';

        $this->config = array_merge(['helpers'=>[]],$config);
        $layout = $this;
        $this->config['helpers'] = array_merge([
            'display'=> function ($id) use($layout) {
                $layout->displayBlock($id);
            },
            'urlAsset'=> function ($name, $options=[], $only=[]) use($layout) {
                $url = '';
                $imgUrls = $layout->config['imgUrls'];
                if (isset($imgUrls[$name])) {
                    $options = Utils::arrayPick($options, $only);
                    $url = $imgUrls[$name];
                    foreach ($options as $name => $o) {
                        $url = str_replace(':'.$name, $o, $url);
                    }
                }
                return $url;
            },
        ], $this->config['helpers']);
    }

    public function setDispatcher (EventDispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function resolve ($id){
        $parentBlock = null;
        $currentBlock = $this->currentBlockInRender;
        $block = $this->registry->get($id);
        if ($currentBlock) {
            $parentBlock = $this->registry->get($currentBlock);
            if ($parentBlock) $parentBlock->displayed_block[] = ['id'=>$id, 'shown'=>!!$block];
        }
        if ($block) {
            $this->currentBlockInRender = $id;
            $block->resolve($this->config['helpers']);
        }
        $this->currentBlockInRender = $currentBlock;
        return $block;
    }
    public function getContent ($id){
        $block = $this->resolve($id);
        if ($block) {
            return $block->body;
        }
        return '';
    }
    public function render (){
        $this->emit('before_layout_render');
        $this->getContent($this->block);
        $this->emit('after_layout_render');
        return $this->getRoot()->body;
    }
    public function getRoot (){
        return $this->get($this->block);
    }

    public function displayBlock ($id){
        $this->emit('before_block_render', $id);
        $this->emit('before_render_' . $id);
        echo $this->getContent($id);
        $this->emit('after_render_' . $id);
        $this->emit('after_block_render', $id);
    }

    /**
     * @param $id
     * @return Block
     */
    public function get ($id){
        return $this->registry->get($id);
    }

    function configureBlock ($block, $options=[]){
        foreach($this->defaultOptions as $n=>$v) {
            if (isset($options[$n]) && is_array($options[$n]) && is_array($v)) {
                $options[$n] = array_merge($options[$n], $v);
            }
        }
        foreach($options as $n=>$v) {
            if (isset($block->{$n}) && is_array($block->{$n}) && is_array($v)) {
                $block->{$n} = array_merge($block->{$n}, $v);
            } else {
                $block->{$n} = $v;
            }
        }
    }

    function getOrCreate ($id){
        if (!($id instanceof Block)) {
            $block = $this->registry->get($id);
            if (!$block) {
                $block = new Block($id);
                $this->registry->set($id, $block);
                if ($this->config['debug']) {
                    $block->stack = Utils::getStackTrace();
                }
            }
        } else {
            $block = $id;
        }
        return $block;
    }

    function set ($id, $options=[]){
        $block = $id instanceof Block ? $id : $this->getOrCreate($id) ;
        $this->configureBlock($block, $options);
        return $block;
    }

    function setMultiple($options=[]){
        foreach($options as $target => $opts) {
            $this->set($target, $opts);
        }
    }

    function remove($id){
        $this->registry->remove($id);
    }

    function keepOnly($pattern){
        $blocks = $this->registry->blocks;
        foreach($blocks as $block) {
            if (!preg_match($pattern, $block->id)) {
                $this->registry->remove($block->id);
            }
        }
    }

    function registerImgPattern($name, $pattern){
        $this->config['imgUrls'][$name] = $pattern;
    }




    public function getTaggedResource () {
        $res = new TagedResource();
        foreach($this->registry->blocks as $block) {
            /* @var $block Block */
            $res->addTaggedResource($block->getTaggedResource());
        }
        return $res;
    }



    public function emit ($id){
        $args = func_get_args();
        $id = array_shift($args);
        $event = new GenericEvent($id, $args);
        if ($this->dispatcher)
            $this->dispatcher->dispatch($id, $event);
    }
    public function on ($id, $fn){
        if ($this->dispatcher)
            call_user_func_array([$this->dispatcher, 'addListener'], func_get_args());
    }
    public function off ($id, $fn){
        if ($this->dispatcher)
            call_user_func_array([$this->dispatcher, ' removeListener'], func_get_args());
    }
    public function beforeRender ($fn){
        $layout = $this;
        $this->on('before_layout_render', function($event) use($layout, $fn){
            $fn($event, $layout);
        });
    }
    public function afterRender ($fn){
        $layout = $this;
        $this->on('after_layout_render', function($event) use($layout, $fn){
            $fn($event, $layout);
        });
    }
    public function beforeRenderAnyBlock ($fn){
        $layout = $this;
        $this->on('before_block_render', function($event) use($layout, $fn){
            /* @var $event \Symfony\Component\EventDispatcher\GenericEvent */
            $fn($event, $layout, $event->getArgument(0));
        });
    }
    public function afterRenderAnyBlock ($fn){
        $layout = $this;
        $this->on('after_block_render', function($event) use($layout, $fn){
            /* @var $event \Symfony\Component\EventDispatcher\GenericEvent */
            $fn($event, $layout, $event->getArgument(0));
        });
    }
    public function beforeBlockRender ($id, $fn){
        $layout = $this;
        $this->on('before_render_'.$id, function($event) use($layout, $id, $fn){
            $fn($event, $layout, $id);
        });
    }
    public function afterBlockRender ($id, $fn){
        $layout = $this;
        $this->on('after_render_'.$id, function($event) use($layout, $id, $fn){
            $fn($event, $layout, $id);
        });
    }


    function traverseBlocksWithStructure (Block $block, Layout $layout, $then, $path=null){
        $parentId = $block->id;
        if ($path===null) {
            $path = "/$parentId";
            $then($parentId, null, $path, ['block'=>$block,'shown'=>true,'exists'=>true]);
        }
        foreach ($block->displayed_block as $displayed_block) {
            $subId = $displayed_block['id'];
            $sub = $layout->get($subId);
            if ($sub) $subId = $sub->id;
            $then($subId, $parentId, "$path/$subId", ['block'=>$sub,'shown'=>$displayed_block['shown'],'exists'=>!!$sub]);
            if ($sub) $this->traverseBlocksWithStructure($sub, $layout, $then, "$path/$subId");
        }
    }
}



