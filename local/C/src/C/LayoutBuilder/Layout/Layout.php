<?php

namespace C\LayoutBuilder\Layout;

use C\Misc\Utils;

class Layout{

    public $block;
    /**
     * @var RegistryBlock
     */
    public $registry;

    public function __construct ($config=[]) {
        $this->registry = new RegistryBlock();
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
        $verbose = $this->config['debug'];
        $content = '';
        if ($block) {
            if ($verbose) $content = "\n".'<!-- begin ' . $block->id .
                ' ' . Utils::shorten($block->options['template']) . ' -->';
            $content .= $block->body;
            if ($verbose) $content .= "\n".'<!-- end ' . $block->id .
                ' ' . Utils::shorten($block->options['template']) . ' -->';
        }
        return $content;
    }


    public function displayBlock ($id){
        $this->emit('before_render_' . $id);
        echo $this->getContent($id);
        $this->emit('after_render_' . $id);
    }


    public function emit ($id){
        if ($this->config['dispatcher'])
        call_user_func_array([$this->config['dispatcher'], 'dispatch'], func_get_args());
    }
    public function on ($id, $fn){
        if ($this->config['dispatcher'])
            call_user_func_array([$this->config['dispatcher'], 'addListener'], func_get_args());
    }
    public function off ($id, $fn){
        if ($this->config['dispatcher'])
            call_user_func_array([$this->config['dispatcher'], ' removeListener'], func_get_args());
    }

    /**
     * @param $id
     * @return Block
     */
    public function get ($id){
        return $this->registry->get($id);
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
}



