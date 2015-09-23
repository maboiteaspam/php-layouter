<?php
namespace C\View;

use C\Layout\Block;
use C\Misc\Utils;
use Symfony\Component\Routing\Generator\UrlGenerator;

class RoutingViewHelper implements ViewHelperInterface {

    /**
     * @var UrlGenerator
     */
    public $generator;
    /**
     * @var Block
     */
    public $block;

    public function __construct () {

    }

    public function setUrlGenerator ( UrlGenerator $generator) {
        $this->generator = $generator;
    }

    public function setBlockToRender ( Block $block) {
        $this->block = $block;
    }

    public function urlFor ($name, $options=[], $only=[]) {
        $options = Utils::arrayPick($options, $only);
        return $this->generator->generate($name, $options);

    }
    public function urlArgs ($data=[], $only=[]) {
        /* @var $block \C\Layout\Block */
        $block = $this->block;
        if (isset($block->meta['from'])) {
            $data = array_merge(Utils::arrayPick($block->meta, ['from']), $data);
        }
        $data = Utils::arrayPick($data, $only);
        $query = http_build_query($data);
        return $query ? '?'.$query : '';
    }
}
