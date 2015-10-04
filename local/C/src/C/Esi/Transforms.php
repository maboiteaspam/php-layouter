<?php
namespace C\Esi;

use C\Layout\Transforms as base;
use C\Layout\Layout;

class Transforms extends base{

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout){
        return new self($layout);
    }

    public function esify($target, $options=[]){
        $options = array_merge(['url'=>'',], $options);
        return $this
            ->forRequest('esi-master')
            ->then(function (Transforms $transform) use ($target, $options) {
                $transform->clearBlock($target
                )->setTemplate($target,
                    'Esi:/esified-block.php'
                )->updateData($target, [
                    'url'   => $options['url'],
                    'target'=> $target,
                ]);
            })
            ->forRequest('esi-slave')
            ->then(function (Transforms $transform) use($target) {
                if ($_GET['target']===$target) {
                    $transform->getLayout()->block = $target;
                }
            })->forRequest('!esi-master');
    }

}
