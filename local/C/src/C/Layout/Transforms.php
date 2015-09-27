<?php
namespace C\Layout;

class Transforms implements TransformsInterface{

    /**
     * @param Layout $layout
     */
    public function __construct(Layout $layout=null){
        if ($layout) $this->setLayout($layout);
    }

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout){
        return new self($layout);
    }

    /**
     * @var \C\Layout\Layout
     */
    public $layout;

    public function setLayout (Layout $layout) {
        $this->layout = $layout;
        return $this;
    }

    public function getLayout () {
        return $this->layout;
    }

    /**
     * stub method
     *
     * @param TransformsInterface $t
     * @return $this
     */
    public function then(TransformsInterface $t=null) {
        // totally wanted this parameter is ignored.
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
    public function clearBlock($id, $what='all'){
        $block = $this->layout->getOrCreate($id);
        if ($block) {
            $block->clear($what);
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
        foreach($assets as $targetAssetGroupName => $files) {
            if(!isset($block->assets[$targetAssetGroupName]))
                $block->assets[$targetAssetGroupName] = [];
            $block->assets[$targetAssetGroupName] = $first
                ? array_merge($files, $block->assets[$targetAssetGroupName])
                : array_merge($block->assets[$targetAssetGroupName], $files);
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



    public function insertAfterBlock ($target, $id, $options){
        $this->layout->set($id, $options);
        $this->layout->afterBlockRender($target, function ($ev, Layout $layout) use($target, $id) {
//            $layout->displayBlock($id);
            $block = $layout->registry->get($target);
            $block->body = $block->body.$layout->getContent($id);
            $block->displayed_block[] = ["id"=>$id, "shown"=>true];
        });
        return $this;
    }

    public function insertBeforeBlock ($target, $id, $options){
        $this->layout->set($id, $options);
        $this->layout->afterBlockRender($target, function ($ev, Layout $layout) use($target, $id) {
//            $layout->displayBlock($id);
            $block = $layout->registry->get($target);
            $block->body = $layout->getContent($id).$block->body;
            $block->displayed_block[] = ["id"=>$id, "shown"=>true];
        });
        return $this;
    }

}
