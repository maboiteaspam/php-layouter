<?php
namespace C\Layout;

class Transforms{

    /**
     * @var \C\Layout\Layout
     */
    public $layout;

    public $app;

    /**
     * @param mixed $app
     */
    public function __construct($app) {
        $this->layout = $app['layout'];
        $this->app = $app;
    }

    /**
     * @param mixed $app
     * @return Transforms
     */
    public static function transform($app) {
        return new Transforms($app);
    }

    /**
     * stub method
     *
     * @param Transforms $t
     * @return $this
     */
    public function then(Transforms $t) {
        return $this;
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

    public function updateAssets($id, $assets=[], $first=false){
        $block = $this->layout->getOrCreate($id);
        foreach($assets as $name => $files) {
            if(!isset($block->assets[$name]))
                $block->assets[$name] = [];
            $block->assets[$name] = $first
                ? array_merge($files, $block->assets[$name])
                : array_merge($block->assets[$name], $files);
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



    public function insertAfter ($target, $id, $options){
        $this->layout->set($id, $options);
        $this->layout->afterBlockRender($target, function ($ev, Layout $layout) use($id) {
            $layout->displayBlock($id);
        });
        return $this;
    }

    public function insertBefore ($target, $id, $options){
        $this->layout->set($id, $options);
        $this->layout->beforeBlockRender($target, function ($ev, Layout $layout) use($id) {
            $layout->displayBlock($id);
        });
        return $this;
    }

}
