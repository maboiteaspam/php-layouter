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
     * @param mixed $some
     * @return $this
     */
    public function then($some=null) {
        if (is_callable($some)) $some($this);
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
    public function deleteBlock($id){
        $this->layout->remove($id);
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

    public function addAssets($id, $assets=[], $first=false){
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
    public function removeAssets($id, $assets=[]){
        $block = $this->layout->getOrCreate($id);
        foreach($assets as $targetAssetGroupName => $files) {
            if(!isset($block->assets[$targetAssetGroupName]))
                $block->assets[$targetAssetGroupName] = [];
            foreach($files as $file) {
                $index = array_search($file, $block->assets[$targetAssetGroupName]);
                if ($index!==false) {
                    array_splice($files, $index, 1);
                }
            }
        }
        return $this;
    }
    public function replaceAssets($id, $replacements=[]){
        $block = $this->layout->getOrCreate($id);
        foreach($replacements as $search => $replacement) {
            foreach ($block->assets as $blockAssetsName=>$blockAssets) {
                foreach($blockAssets as $i=>$asset) {
                    if ($asset===$search) {
                        $block->assets[$blockAssetsName][$i] = $replacement;
                    }
                }
            }
        }
        return $this;
    }

    public function sefDefaultData($id, $data=[]){
        $block = $this->layout->getOrCreate($id);
        $block->data = array_merge($data, $block->data);
        return $this;
    }
    public function updateData($id, $data=[]){
        $block = $this->layout->getOrCreate($id);
        $block->data = array_merge($block->data, $data);
        return $this;
    }

    public function addIntl($id, $intl, $locale, $domain=null){
        $block = $this->layout->getOrCreate($id);
        $block->intl[] = [
            'item'=>$intl,
            'locale'=>$locale,
            'domain'=>$domain,
        ];
        return $this;
    }

    public function replaceIntl($search, $replace){
        foreach ($this->layout->registry->blocks as $i=>$block) {
            foreach ($block->intl as $e=>$intl) {
                if ($intl['item']===$search) {
                    $this->layout->registry->blocks[$i]->intl[$e]['item'] = $replace;
                }
            }
        }
        return $this;
    }

    public function removeIntl($search){
        foreach ($this->layout->registry->blocks as $i=>$block) {
            foreach ($block->intl as $e=>$intl) {
                if ($intl['item']===$search) {
                    unset($this->layout->registry->blocks[$i]->intl[$e]);
                }
            }
        }
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


    /**
     * switch to a device type
     * desktop, mobile, tablet
     * default is desktop
     *
     * @param $device
     * @return $this|VoidTransforms
     */
    public function forDevice ($device) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isDevice'], func_get_args())) {
            return $this;
        }
        return new VoidTransforms($this);
    }

    /**
     * switch to a request kind
     * ajax, get
     * default is get
     * esi-slave, esi-master are esi internals.
     * it can also receive negate kind such
     * !ajax !esi-master !esi-slave !get
     *
     * @param $kind
     * @return $this|VoidTransforms
     */
    public function forRequest ($kind) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isRequestKind'], func_get_args())) {
            return $this;
        }
        return new VoidTransforms($this);
    }
    public function forLang ($lang) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isLang'], func_get_args())) {
            return $this;
        }
        return new VoidTransforms($this);
    }

    public function insertAfterBlock ($target, $id, $options=[]){
        $this->layout->set($id, $options);
        $this->layout->afterBlockResolve($target, function ($ev, Layout $layout) use($target, $id) {
            $layout->resolve($id);
        });
        $this->layout->afterBlockRender($target, function ($ev, Layout $layout) use($target, $id) {
            $block = $layout->registry->get($target);
            if ($block) {
                $block->body = $block->body.$layout->getContent($id);
                $block->displayed_block[] = ["id"=>$id, "shown"=>true];
            }
        });
        return $this;
    }

    public function insertBeforeBlock ($beforeTarget, $id, $options=[]){
        $this->layout->set($id, $options);
        $this->layout->beforeBlockResolve($beforeTarget, function ($ev, Layout $layout) use($beforeTarget, $id) {
            $layout->resolve($id);
        });
        $this->layout->afterBlockRender($beforeTarget, function ($ev, Layout $layout) use($beforeTarget, $id) {
//            $layout->displayBlock($id);
            $block = $layout->registry->get($beforeTarget);
            $block->body = $layout->getContent($id).$block->body;
            $block->displayed_block[] = ["id"=>$id, "shown"=>true];
        });
        return $this;
    }

}
