<?php
namespace C\LayoutBuilder;

use C\LayoutBuilder\Layout\Layout;
use C\Misc\Utils;
use C\Data\TaggedData;

class Transforms{

    public $layout;

    /**
     * @param Layout $layout
     */
    public function __construct(Layout $layout) {
        $this->layout = $layout;
    }

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout) {
        return new Transforms($layout);
    }

    public function set($id, $options){
        $this->layout->set($id, $options);
        return $this;
    }
    public function setTemplate($id, $template){
        $block = $this->layout->getOrCreate($id);
        if ($block) {
            $block->options['template'] = $template;
        }
        return $this;
    }
    public function setBody($id, $body){
        $block = $this->layout->getOrCreate($id);
        if ($block) {
            $block->body = $body;
        }
        return $this;
    }
    public function updateOptions($id, $options=[]){
        $block = $this->layout->getOrCreate($id);
        $block->options = array_merge($options, $block->options);
        return $this;
    }

    public function updateAssets($id, $assets=[]){
        $block = $this->layout->getOrCreate($id);
        foreach($assets as $name => $files) {
            if(!isset($block->assets[$name]))
                $block->assets[$name] = [];
            $block->assets[$name] =
                array_merge($block->assets[$name], $files);
        }
        return $this;
    }

    public function updateData($id, $data=[]){
        $block = $this->layout->getOrCreate($id);
        $block->data = array_merge($block->data, $data);
        return $this;
    }

    public function updateMeta($id, $meta=[]){
        $block = $this->layout->getOrCreate($id);
        $block->meta = array_merge($block->meta, $meta);
        return $this;
    }

    public function updateBlock($id, $meta=[], $data=[], $options=[]){
        $block = $this->layout->getOrCreate($id);
        $block->meta = array_merge($block->meta, $meta);
        $block->data = array_merge($block->data, $data);
        $block->options = array_merge($block->options, $options);
        return $this;
    }

    public function keepOnly($pattern){
        $this->layout->keepOnly($pattern);
        return $this;
    }


    public function updateEtags(){
        foreach($this->layout->registry->blocks as $block) {
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
        return $this;
    }

    public function insertAfter ($target, $id, $options){
        $this->layout->set($id, $options);
        $layout = $this->layout;
        $this->layout->on('after_render_' . $target, function () use($layout, $id) {
            $layout->displayBlock($id);
        });
        return $this;
    }

    public function insertBefore ($target, $id, $options){
        $this->layout->set($id, $options);
        $layout = $this->layout;
        $this->layout->on('before_render_' . $target, function () use($layout, $id) {
            $layout->displayBlock($id);
        });
        return $this;
    }

}
