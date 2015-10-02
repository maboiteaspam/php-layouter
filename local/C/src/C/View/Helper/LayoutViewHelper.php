<?php
namespace C\View\Helper;

use C\Layout\Layout;

class LayoutViewHelper extends AbstractViewHelper {

    /**
     * @var Layout
     */
    public $layout;

    public function setLayout ( Layout $layout) {
        $this->layout = $layout;
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
