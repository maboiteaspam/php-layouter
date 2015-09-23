<?php
namespace C\View;

use C\Layout\Block;
use C\Misc\Utils;

class AssetsViewHelper implements ViewHelperInterface {


    /**
     * @var Block
     */
    public $block;

    public function setBlockToRender ( Block $block) {
        $this->block = $block;
    }

    public $assetPatterns = [];

    public function setPatterns ($patterns) {
        $this->assetPatterns = $patterns;
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




}
