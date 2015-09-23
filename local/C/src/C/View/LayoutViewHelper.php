<?php
namespace C\View;

use C\Layout\Block;
use C\Layout\Layout;

class LayoutViewHelper implements ViewHelperInterface {

    /**
     * @var Layout
     */
    public $layout;

    public function setLayout ( Layout $layout) {
        $this->layout = $layout;
    }

    /**
     * @var Block
     */
    public $block;

    public function setBlockToRender ( Block $block) {
        $this->block = $block;
    }

    public function display ($blockId) {
        $layout = $this->layout;
        $layout
            ->registry->get($layout->currentBlockInRender)
            ->displayed_block[] = [
            'id'    => $blockId,
            'shown' => $layout->registry->has($blockId)
        ];
        echo "<!-- placeholder for block $blockId -->";
    }
}
