<?php
namespace C\ModernApp\jQuery;

use C\Layout\Layout;
use C\Layout\TransformsInterface;

class VoidTransforms implements TransformsInterface{

    /**
     * @param Layout $layout
     */
    public function __construct(Layout $layout=null){
        if ($layout) $this->setLayout($layout);
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
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout){
        return new self($layout);
    }

    public function __call ($a, $b) {
        return $this;
    }
}
