<?php
namespace C\HTMLLayoutBuilder\Layout;

use C\LayoutBuilder\Layout\Block as Base;

class Block extends Base{
    public function appendCss($path, $to='template_head_css'){
        $this->assets[$to][] = $path;
    }
    public function appendJs($path, $to='template_footer_js'){
        $this->assets[$to][] = $path;
    }
}
