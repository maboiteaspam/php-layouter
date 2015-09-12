<?php

namespace C\LayoutBuilder\Layout;

use Symfony\Component\EventDispatcher\GenericEvent;
use C\Misc\Utils;

class Layout{

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

    public $defaultOptions = [
        'options'=>[]
    ];

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

    public function resolve ($id){
        $block = $this->registry->get($id);
        if ($block) {
            $block->resolve($this->config['helpers']);
        }
        return $block;
    }
    public function getContent ($id){
        $block = $this->resolve($id);
        $content = '';
        if ($block) {
            $content .= $block->body;
        }
        return $content;
    }
    public function render (){
        return $this->getContent($this->block);
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

    function setRoot ($options){
        return $this->set( 'root', $options);
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



    /**
     * @return String
     */
    public function getEtag (){
        $h = '';
        foreach($this->registry->blocks as $block) {
            $h .= $block->meta['etag'] . '-';
        }
        return sha1($h);
    }


    public function emit ($id){
        $args = func_get_args();
        $id = array_shift($args);
        $event = new GenericEvent($id, $args);
        if ($this->config['dispatcher'])
            $this->config['dispatcher']->dispatch($id, $event);
    }
    public function on ($id, $fn){
        if ($this->config['dispatcher'])
            call_user_func_array([$this->config['dispatcher'], 'addListener'], func_get_args());
    }
    public function off ($id, $fn){
        if ($this->config['dispatcher'])
            call_user_func_array([$this->config['dispatcher'], ' removeListener'], func_get_args());
    }
}



