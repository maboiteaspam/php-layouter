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

    public function display ($blockId, $force=false) {
        $layout = $this->layout;
        if ($force) $layout->getOrCreate($blockId);
        $layout
            ->registry->get($layout->currentBlockInRender)
            ->displayed_block[] = [
            'id'    => $blockId,
            'shown' => $layout->registry->has($blockId)
        ];
        echo "<!-- placeholder for block $blockId -->";
    }
}
