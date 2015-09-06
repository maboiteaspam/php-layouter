<?php
namespace C\LayoutBuilder;

use C\LayoutBuilder\Layout\Builder;
use C\LayoutBuilder\Layout\Layout;
use C\Misc\Utils;
use C\Data\TaggedData;

class Transforms{

    public static function set($id, $options){
        return function(Layout $layout) use($id, $options){
            Builder::set($layout, $id, $options);
        };
    }
    public static function setTemplate($id, $template){
        return function(Layout $layout) use($id, $template){
            $block = Builder::getOrCreate($layout, $id);
            if ($block) {
                $block->options['template'] = $template;
            }
        };
    }
    public static function updateOptions($id, $options=[]){
        return function(Layout $layout) use($id, $options){
            $block = Builder::getOrCreate($layout, $id);
            $block->options = array_merge($options, $block->options);
        };
    }

    public static function updateAssets($id, $assets=[]){
        return function(Layout $layout) use($id, $assets){
            $block = Builder::getOrCreate($layout, $id);
            foreach($assets as $name => $files) {
                if(!isset($block->assets[$name]))
                    $block->assets[$name] = [];
                $block->assets[$name] =
                    array_merge($block->assets[$name], $files);
            }
        };
    }

    public static function updateBlock($id, $meta=[], $data=[], $options=[]){
        return function(Layout $layout) use($id, $meta, $data, $options){
            $block = Builder::getOrCreate($layout, $id);
            $block->meta = array_merge($block->meta, $meta);
            $block->data = array_merge($block->data, $data);
            $block->options = array_merge($block->options, $options);
        };
    }

    public static function keepOnly($pattern){
        return function(Layout $layout) use($pattern){
            Builder::keepOnly($layout, $pattern);
        };
    }


    public static function updateEtags(){
        return function(Layout $layout){
            foreach($layout->registry->blocks as $block) {
                $h = '';
                $h .= $block->id . '-';
                if ($block->options['template']) {
                    $h .= Utils::fileToEtag($block->options['template']);
                }
                foreach($block->assets as $assets) {
                    $h .= Utils::fileToEtag($assets);
                }
                array_walk_recursive($block->data, function($data) use(&$h){
                    if ($data instanceof TaggedData) {
                        $h .= serialize($data->etag());
                    } else {
                        $h .= serialize($data);
                    }
                });
                $block->meta['etag'] = sha1($h);
            }
        };
    }

    public static function insertAfter ($target, $id, $options){
        return function(Layout $layout) use($target, $id, $options){
            Builder::set($layout, $id, $options);
            $layout->on('after_render_' . $target, function () use($layout, $id) {
                $layout->displayBlock($id);
            });
        };
    }

    public static function insertBefore ($target, $id, $options){
        return function(Layout $layout) use($target, $id, $options){
            Builder::set($layout, $id, $options);
            $layout->on('before_render_' . $target, function () use($layout, $id) {
                $layout->displayBlock($id);
            });
        };
    }

}
