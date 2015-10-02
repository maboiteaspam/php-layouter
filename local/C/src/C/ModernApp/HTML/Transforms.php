<?php
namespace C\ModernApp\HTML;

use C\Layout\Transforms as BaseTransforms;
use Silex\Application;
use C\Layout\Layout;

class Transforms extends BaseTransforms{

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout){
        return new self($layout);
    }

    public function baseTemplate ($bodyTemplate='HTML:/1-column.php') {
        $this->setTemplate('root',
            'HTML:/html.php'
        )->set('html_begin', [
            'body'  => "<!DOCTYPE html>\n<html>\n"
        ])->setTemplate('html_head',
            'HTML:/head.php'
        )->set('html_body_begin', [
                'body'  => "\n<body>"
        ])->set('body', [
            'options'=>[
                'template' => $bodyTemplate
            ],
        ])->set('html_body_end', [
            'body'  => "\n</body>"
        ])->setTemplate('footer',
            'HTML:/footer.php'
        )->set('script_bottom',  [
            'body'  => ''
        ])->set('html_end',[
            'body'  => '</html>'
        ]);
        return $this;
    }

}
