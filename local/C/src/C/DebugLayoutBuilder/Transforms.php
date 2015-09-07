<?php
namespace C\DebugLayoutBuilder;

use C\LayoutBuilder\Transforms as BaseTransforms;
use C\Misc\Utils;
use C\LayoutBuilder\Layout\Layout;
use Symfony\Component\EventDispatcher\GenericEvent;

class Transforms extends BaseTransforms{

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout) {
        return new Transforms($layout);
    }

    public function debug (){
        $verbose = isset($this->layout->config['verbose'])?$this->layout->config['verbose']:false;
        $debug = isset($this->layout->config['debug'])?$this->layout->config['debug']:false;
        $layout = $this->layout;

        $this->layout->on('before_block_render', function (GenericEvent $event) use($layout, $verbose, $debug) {
            $id = $event->getArgument(0);
            $block = $layout->get($id);
            $p = Utils::shorten($block->options['template']);
            if ($verbose) echo "\n".'<!-- begin ' . $block->id . ' ' . $p . ' -->';
            if ($debug) echo '<c_block_node id="'.$id.'">';
        });

        $this->layout->on('after_block_render', function (GenericEvent $event) use($layout, $verbose, $debug) {
            $id = $event->getArgument(0);
            $block = $layout->get($id);
            $p = Utils::shorten($block->options['template']);
            if ($debug) echo '</'.'c_block_node>';
            if ($verbose) echo "\n".'<!-- end ' . $block->id . ' ' . $p . ' -->';
        });

        $this->updateAssets('body', [
            'template_head_css'=>[
                __DIR__ . '/assets/index.css'
            ],
            'page_footer_js'=>[
                __DIR__ . '/assets/index.js'
            ],
        ]);

        return $this;
    }
}
