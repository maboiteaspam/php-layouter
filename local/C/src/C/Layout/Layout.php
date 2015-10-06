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
     * Layout's description
     *
     * @var string
     */
    public $description;

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
     * @var RequestTypeMatcher
     */
    public $requestMatcher;
    /**
     * @var KnownFs
     */
    public $fs;
    /**
     * this let you inject extra
     * resources tags to apply on
     * the layout level
     * @var array
     */
    public $globalResourceTags = [];

    /**
     * The default options for each
     * new block
     * @var array
     */
    public $defaultOptions = [
        'options'=>[],
        'meta'=>[],
    ];

    /**
     * @var string
     */
    public $currentBlockInRender;

    public function __construct ($config=[]) {
        $this->registry = new RegistryBlock();
        $this->block = 'root';
        $this->config = array_merge([], $config);
    }

    #region initialization
    public function setRequestMatcher (RequestTypeMatcher $requestMatcher) {
        $this->requestMatcher = $requestMatcher;
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
    public function setDescription ($description) {
        $this->description = $description;
    }

    public function setContext (Context $ctx) {
        $this->context = $ctx;
    }
    #endregion

    #region block rendering
    public function resolve ($id){
        $parentBlock = null;
        $this->emit('before_block_resolve', $id);
        $this->emit('before_resolve_' . $id);
        $block = $this->registry->get($id);
        $currentBlockInRender = $this->currentBlockInRender;
        $this->currentBlockInRender = $id;
        if ($block) {
            $block->resolve($this->fs, $this->context);
        }
        $this->currentBlockInRender = $currentBlockInRender;
        $this->emit('after_block_resolve', $id);
        $this->emit('after_resolve_' . $id);
        return $block;
    }

    public function getContent ($id) {
        $body = "";
        $block = $this->registry->get($id);
        if ($block) {
            if(!$block->resolved) {
                $currentBlockInRender = $this->currentBlockInRender;
                $this->currentBlockInRender = $id;
                $block->resolve($this->fs, $this->context);
                $this->currentBlockInRender = $currentBlockInRender;
            }
            $this->emit('before_block_render', $id);
            $this->emit('before_render_' . $id);
            $body = $block->body;
            foreach($block->getDisplayedBlocksId() as $displayedBlock) {
                $body = str_replace("<!-- placeholder for block $displayedBlock -->",
                    $this->getContent($displayedBlock),
                    $body);
            }
            $block->body = $body;
        } else {
            $this->emit('before_block_render', $id);
            $this->emit('before_render_' . $id);
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
    public function resolveInCascade ($startBlock){
        $layout = $this;
        $layout->resolve($startBlock);
        $block = $this->get($startBlock);
        if ($block) {
            foreach ($block->getDisplayedBlocksId() as $id) {
                $this->resolveInCascade($id);
            }
        }
    }
    public function render (){
        $this->emit('before_layout_resolve');
//            $this->resolveAllBlocks ();
        $this->resolveInCascade($this->block);
        $this->emit('after_layout_resolve');

        $this->emit('before_layout_render'); // mhh
        $this->getContent ($this->block);
        $this->emit('after_layout_render');

        return $this->getRoot()->body;
    }

    public function displayBlock ($id){
        echo $this->getContent($id);
    }
    #endregion

    #region block manipulation
    public function getRoot (){
        return $this->get($this->block);
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
    #endregion

    #region bulk block manipulation
    function keepOnly($pattern){
        $blocks = $this->registry->blocks;
        foreach($blocks as $block) {
            if (!preg_match($pattern, $block->id)) {
                $this->registry->remove($block->id);
            }
        }
    }
    #endregion



    #region resource tagging
    public function addGlobalResourceTag (TagedResource $resource) {
        $this->globalResourceTags[] = $resource;
    }
    public function addGlobalTag ($tag, $type) {
        $res = new TagedResource();
        $res->addResource($tag, $type);
        $this->globalResourceTags[] = $res;
    }
    public function getDisplayedBlocksId($blockId) {
        $displayed = [];
        $block = $this->get($blockId);
        $displayed = array_merge($displayed, $block->getDisplayedBlocksId());
        foreach ($displayed as $d) {
            $displayed = array_merge($displayed, $this->getDisplayedBlocksId($d));
        }
        return ($displayed);
    }
    public function excludedBlocksFromTagResource() {
        $excluded = [];
        foreach($this->registry->blocks as $block /* @var $block Block */) {
            if (isset($block->options['tagresource_excluded'])
                && $block->options['tagresource_excluded']) {
                $excluded = array_merge($excluded, [$block->id], $this->getDisplayedBlocksId($block->id));
            }
        }
        return array_unique($excluded);
    }
    /**
     * @return bool|TagedResource
     */
    public function getTaggedResource () {
        $res = new TagedResource();
        $excluded = $this->excludedBlocksFromTagResource();
        try{
            $res->addResource($this->debugEnabled?'with-debug':'without-debug');
            $res->addResource($this->block);
            $res->addResource($this->requestMatcher->getTaggedResource());
            foreach($this->globalResourceTags as $extra) {
                $res->addTaggedResource($extra);
            }
            foreach($this->registry->blocks as $block) {
                if ($block->resolved
                    && !in_array($block->id, $excluded)) {
                    /* @var $block Block */
                    $res->addTaggedResource($block->getTaggedResource());
                }
            }
        }catch(\Exception $ex) {
            $res = false;
        }
        return $res;
    }
    #endregion



    #region event dispatching
    public function emit ($id){
        $args = func_get_args();
        $id = array_shift($args);
        $event = new GenericEvent($id, $args);
        if ($this->dispatcher)
            $this->dispatcher->dispatch($id, $event);
    }
    /**
     * @param string   $eventName The event to listen on
     * @param callable $listener  The listener
     * @param int      $priority  The higher this value, the earlier an event
     *                            listener will be triggered in the chain (defaults to 0)
     */
    public function on ($eventName, $listener, $priority=0){
        if ($this->dispatcher)
            call_user_func_array([$this->dispatcher, 'addListener'], func_get_args());
    }
    public function off ($id, $fn){
        if ($this->dispatcher)
            call_user_func_array([$this->dispatcher, ' removeListener'], func_get_args());
    }
    public function onControllerBuildFinish ($fn){
        $layout = $this;
        $this->on('controller_build_finish', function($event) use($layout, $fn){
            $fn($event, $layout, $event->getArgument(0));
        });
    }
    public function onLayoutBuildFinish ($fn){
        $layout = $this;
        $this->on('layout_build_finish', function($event) use($layout, $fn){
            $fn($event, $layout, $event->getArgument(0));
        });
    }
    /**
     * @param callable $listener  The listener
     * @param int      $priority  The higher this value, the earlier an event
     *                            listener will be triggered in the chain (defaults to 0)
     */
    public function beforeRender ($listener, $priority=0) {
        $layout = $this;
        $this->on('before_layout_render', function($event) use($layout, $listener){
            $listener($event, $layout);
        }, $priority);
    }
    /**
     * @param callable $listener  The listener
     * @param int      $priority  The higher this value, the earlier an event
     *                            listener will be triggered in the chain (defaults to 0)
     */
    public function afterRender ($listener, $priority=0) {
        $layout = $this;
        $this->on('after_layout_render', function($event) use($layout, $listener){
            $listener($event, $layout);
        }, $priority);
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

    public function beforeResolve ($listener, $priority=0) {
        $layout = $this;
        $this->on('before_layout_resolve', function($event) use($layout, $listener){
            $listener($event, $layout);
        }, $priority);
    }
    public function afterResolve ($listener, $priority=0) {
        $layout = $this;
        $this->on('after_layout_resolve', function($event) use($layout, $listener){
            $listener($event, $layout);
        }, $priority);
    }
    public function beforeResolveAnyBlock ($fn){
        $layout = $this;
        $this->on('before_block_resolve', function($event) use($layout, $fn){
            /* @var $event \Symfony\Component\EventDispatcher\GenericEvent */
            $fn($event, $layout, $event->getArgument(0));
        });
    }
    public function afterResolveAnyBlock ($fn){
        $layout = $this;
        $this->on('after_block_resolve', function($event) use($layout, $fn){
            /* @var $event \Symfony\Component\EventDispatcher\GenericEvent */
            $fn($event, $layout, $event->getArgument(0));
        });
    }
    public function beforeBlockResolve ($id, $fn){
        $layout = $this;
        $this->on('before_resolve_'.$id, function($event) use($layout, $id, $fn){
            $fn($event, $layout, $id);
        });
    }
    public function afterBlockResolve ($id, $fn){
        $layout = $this;
        $this->on('after_resolve_'.$id, function($event) use($layout, $id, $fn){
            $fn($event, $layout, $id);
        });
    }
    #endregion


    #region serializer
    /**
     * @var LayoutSerializer
     */
    public $serializer;

    public function setLayoutSerializer (LayoutSerializer $serializer) {
        $this->serializer = $serializer;
    }
    #endregion

    #region block iteration
    function traverseBlocksWithStructure (Block $block, Layout $layout, $then, $path=null){
        $parentId = $block->id;
        if ($path===null) {
            $path = "/$parentId";
            $then($parentId, null, $path, ['block'=>$block,'shown'=>true,'exists'=>true]);
        }
        foreach ($block->displayed_blocks as $displayed_block) {

            $subId = $displayed_block['id'];
            $sub = $layout->get($subId);
            if ($sub) $subId = $sub->id;

            $then($subId,
                $parentId,
                "$path/$subId",
                ['block'=>$sub,'shown'=>$displayed_block['shown'],'exists'=>!!$sub]);

            if ($sub) $this->traverseBlocksWithStructure($sub, $layout, $then, "$path/$subId");
        }
    }
    #endregion
}



