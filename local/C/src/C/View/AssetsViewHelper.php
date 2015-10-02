<?php
namespace C\View;

use C\Layout\Block;
use C\Misc\Utils;

class AssetsViewHelper implements ViewHelperInterface {


    /**
     * @var Block
     */
    public $block;
    public $currentInline;

    public function setBlockToRender ( Block $block) {
        $this->block = $block;
//        if ($this->currentInline!==null) echo 'bad';
        $this->currentInline = null;
    }

    public $assetPatterns = [];

    public function setPatterns ($patterns) {
        $this->assetPatterns = $patterns;
    }

    public function addPattern ($pattern) {
        $this->assetPatterns[] = $pattern;
    }


    public function urlAsset ($name, $options=[], $only=[]) {
        $url = '';
        $imgUrls = $this->assetPatterns;
        if (isset($imgUrls[$name])) {
            $options = Utils::arrayPick($options, $only);
            $url = $imgUrls[$name];
            foreach ($options as $name => $o) {
                $url = str_replace(':'.$name, $o, $url);
            }
        }
        return $url;
    }


    /**
     * starts recording of a script/css inline.
     * $target is first head foot last
     * @param $target
     */
    public function inlineTo ($target) {
        $this->currentInline = $target;
//        if ($this->currentInline!==null) echo 'bad';
        ob_start();
    }

    public function endInline() {
//        if ($this->currentInline===null) echo 'bad';
        $content = ob_get_clean();
        $type = strpos($content, "script")!==false?"js":"css";
        $this->block->addInline($this->currentInline, $type, $content);
        $this->currentInline = null;
    }


}
