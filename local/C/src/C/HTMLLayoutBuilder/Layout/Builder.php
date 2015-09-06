<?php
namespace C\HTMLLayoutBuilder\Layout;

use C\LayoutBuilder\Layout\Layout as BaseLayout;
use C\LayoutBuilder\Layout\Block as BaseBlock;
use C\LayoutBuilder\Layout\Builder as Base;

class Builder extends Base{

    static function getOrCreate (BaseLayout $layout, $id){
        if (!($id instanceof BaseBlock)) {
            $block = $layout->registry->get($id);
            if (!$block) {
                $block = new Block($id);
                $layout->registry->set($id, $block);
            }
        } else {
            $block = $id;
        }
        return $block;
    }
}
