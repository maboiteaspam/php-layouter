<?php
namespace C\DebugLayoutBuilder;

use C\LayoutBuilder\Transforms as BaseTransforms;
use C\Misc\Utils;
use C\LayoutBuilder\Layout\Layout;
use Symfony\Component\EventDispatcher\GenericEvent;
use C\jQueryLayoutBuilder\Transforms as jQueryTransforms;

class Transforms extends BaseTransforms{

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout) {
        return new Transforms($layout);
    }

    public function debug ($fromClass=""){
        $verbose = isset($this->layout->config['verbose'])?$this->layout->config['verbose']:false;
        $debug = isset($this->layout->config['debug'])?$this->layout->config['debug']:false;
        $layout = $this->layout;

        $this->layout->on('before_block_render', function (GenericEvent $event) use($layout, $fromClass, $verbose, $debug) {
            $id = $event->getArgument(0);
            $block = $layout->get($id);
            $p = $id;
            if (!$block) {
                $p = 'not defined in this layout';
            } else {
                $p .= '';
                $p .= isset($block->options['template'])
                    ? ' defined in ' . Utils::shorten($block->options['template'])
                    : ' no template';
                $caller = ($debug||$verbose) && $block? Utils::findCaller($block->stack, $fromClass) : [];
                if ($debug && $caller) {
                    $caller['file'] = Utils::shorten($caller['file']);
                    $p .= ', called from ';
                    $p .= isset($caller['file'])?$caller['file']:$caller['class'];
                    $p .= isset($caller['line'])?' at line '.$caller['line']:'';
                }
            }
            if ($verbose) echo "\n".'<!-- begin ' . $block->id . ' ' . $p . ' -->';
            if ($debug) echo '<c_block_node id="'.$id.'" caller="'.str_replace("\\","\\\\",$p).'">';
        });

        $this->layout->on('after_block_render', function (GenericEvent $event) use($layout, $fromClass, $verbose, $debug) {
            $id = $event->getArgument(0);
            $block = $layout->get($id);
            $p = $id;
            if ($block) {
                $p = 'virtual';
            } else {
                $p .= ' defined in ';
                $p .= isset($block->options['template'])
                    ? Utils::shorten($block->options['template'])
                    : 'no template';
                $caller = ($debug||$verbose) && $block? Utils::findCaller($block->stack, $fromClass) : [];
                if ($debug && $caller) {
                    $caller['file'] = Utils::shorten($caller['file']);
                    $p .= ', called from ';
                    $p .= isset($caller['file'])?$caller['file']:$caller['class'];
                    $p .= isset($caller['line'])?' at line '.$caller['line']:'';
                }
            }
            if ($debug) echo '</'.'c_block_node>';
            if ($verbose) echo "\n".'<!-- end ' . $id . ' ' . $p . ' -->';
        });

        jQueryTransforms::transform($this->layout)->tooltipster();
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
