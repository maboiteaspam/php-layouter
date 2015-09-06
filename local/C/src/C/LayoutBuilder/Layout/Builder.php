<?php

namespace C\LayoutBuilder\Layout;

class Builder{

    public static $defaultOptions = [
        'options'=>[]
    ];

    static function setRoot (Layout $layout, $options){
        $layout->block = 'root';
        return static::set($layout, 'root', $options);
    }

    static function configureBlock ($block, $options=[]){
        foreach(self::$defaultOptions as $n=>$v) {
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

    static function getOrCreate (Layout $layout, $id){
        if (!($id instanceof Block)) {
            $block = $layout->registry->get($id);
            if (!$block) {
                $block = new Block($id);
                $layout->registry->set($id, $block);
            }
        } else {
            $block = $id;
        }
        return $block;
    }

    static function set (Layout $layout, $id, $options=[]){
        $block = $id instanceof Block ? $id : static::getOrCreate($layout, $id) ;
        static::configureBlock($block, $options);
        return $block;
    }

    static function setMultiple(Layout $layout, $options=[]){
        foreach($options as $target => $opts) {
            static::set($layout, $target, $opts);
        }
    }

    static function remove(Layout $layout, $id){
        $layout->registry->remove($id);
    }

    static function keepOnly(Layout $layout, $pattern){
        $blocks = $layout->registry->blocks;
        foreach($blocks as $block) {
            if (!preg_match($pattern, $block->id)) {
                $layout->registry->remove($block->id);
            }
        }
    }

}
