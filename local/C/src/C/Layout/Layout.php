<?php

namespace C\Layout;

use C\TagableResource\TagedResource;
use C\TagableResource\TagableResourceInterface;
use C\View\Context;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;
use C\Misc\Utils;
use C\FS\KnownFs;

class Layout implements TagableResourceInterface{

    /**
     * Layout's id
     *
     * @var string
     */
    public $id;

    /**
     * id of the block to start display from
     *
     * @var string
     */
    public $block;

    /**
     * The context that templates use to execute.
     * IE: It will bind $this to that object instance.
     *
     * @var Context
     */
    public $context;

    /**
     * @var RegistryBlock
     */
    public $registry;

    /**
     * enable or disable debug tools.
     *
     * @var bool
     */
    public $debugEnabled;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    public $dispatcher;
    /**
     * @var KnownFs
     */
    public $fs;

    public $defaultOptions = [
        'options'=>[]
    ];

    /**
     * @var string
     */
    public $currentBlockInRender;

    public function __construct ($config=[]) {
        $this->registry = new RegistryBlock();
        $this->block = 'root';
        $this->config = array_merge(['helpers'=>[]],$config);
    }

    public function setDispatcher (EventDispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function setFS (KnownFs $fs) {
        $this->fs = $fs;
    }

    public function enableDebug ($enabled) {
        $this->debugEnabled = $enabled;
    }

    public function setId ($layoutId) {
        $this->id = $layoutId;
    }

    public function setContext (Context $ctx) {
        $this->context = $ctx;
    }

    public function resolve ($id){
        $parentBlock = null;
        $this->emit('before_block_resolve', $id);
        $block = $this->registry->get($id);
        $currentBlockInRender = $this->currentBlockInRender;
        $this->currentBlockInRender = $id;
        if ($block) {
            $block->resolve($this->fs, $this->context);
        }
        $this->currentBlockInRender = $currentBlockInRender;
        $this->emit('after_block_resolve', $id);
        return $block;
    }

    public function getContent ($id) {
        $body = "";
        $this->emit('before_block_render', $id);
        $this->emit('before_render_' . $id);
        $block = $this->registry->get($id);
        if ($block) {
            $body = $block->body;
            foreach($block->displayed_block as $displayedBlock) {
                $body = str_replace("<!-- placeholder for block ".$displayedBlock['id']." -->",
                    $this->getContent($displayedBlock['id']),
                    $body);
            }
            $block->body = $body;
        }
        $this->emit('after_render_' . $id);
        $this->emit('after_block_render', $id);
        if ($block) {
            $body = $block->body;
        }
        return $body;
    }
    public function resolveAllBlocks (){
        $layout = $this;
        $this->registry->each(function ($block) use($layout) {
            $layout->resolve($block->id);
        });
    }
    public $hasPreRendered = false;
    public function preRender (){
        if (!$this->hasPreRendered) {
            $this->emit('before_layout_render');
            $this->resolveAllBlocks ();
            $this->hasPreRendered = true;
            return true;
        }
        return false;
    }
    public function render (){
        $this->preRender();
        $this->getContent ($this->block);
        $this->emit('after_layout_render');
        return $this->getRoot()->body;
    }
    public function getRoot (){
        return $this->get($this->block);
    }

    public function displayBlock ($id){
        echo $this->getContent($id);
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
                if ($this->debugEnabled) {
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




    public function getTaggedResource () {
        $res = new TagedResource();
        try{
            $res->addResource($this->block);
            foreach($this->registry->blocks as $block) {
                /* @var $block Block */
                $res->addTaggedResource($block->getTaggedResource());
            }
        }catch(\Exception $ex) {
            $res = false;
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



