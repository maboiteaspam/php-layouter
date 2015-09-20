<?php
namespace C\ModernApp\Dashboard;

use C\Layout\Transforms as Base;
use C\Layout\Layout;
use C\Misc\Utils;

class Transforms extends Base{

    /**
     * @param mixed $app
     * @return Transforms
     */
    public static function transform ($app) {
        return new Transforms($app);
    }

    public function show ($show=true, $fromClass=''){

        if (!$show) return $this;

        $app = $this->app;
        /* @var $layout \C\Layout\Layout */
        $layout = $app['layout'];

        $this->insertBefore('html_end', 'dashboard', [
            'options' => [
                'template'=>__DIR__.'/templates/dashboard.php'
            ]
        ])->updateAssets('dashboard', [
            'template_head_css'=>[
                __DIR__ . '/assets/dashboard.css'
            ],
            'page_footer_js'=>[
                __DIR__ . '/assets/dashboard.js'
            ],
        ]);

        $this->set('dashboard-layout', [
            'body' => "<!-- placeholder layout structure -->",
        ])->updateAssets('dashboard-layout', [
            'template_head_css'=>[
                __DIR__ . '/assets/layout-structure.css'
            ],
            'page_footer_js'=>[
                __DIR__ . '/assets/layout-structure.js'
            ],
        ]);

        $this->insertAfter('root', 'dashboard-layout-structure', [
            'options' => [
                'template'=>__DIR__.'/templates/layout-structure.php'
            ],
            'data' => [
                'struct'=> function () use($layout) {
                    $struct = [];
                    $root = $layout->get($layout->block);
                    $layout->traverseBlocksWithStructure($root, $layout, function ($blockId, $parentId, $path, $options) use(&$struct) {
                        $block = $options['block'];
                        $template = 'inlined body';
                        $assets = [];

                        if ($block) {
                            if (isset($block->options['template']) && $block->options['template'])
                                $template = Utils::shorten($block->options['template']);
                            foreach ($block->assets as $assetGroup=>$assetsGroup) {
                                if (!isset($assets[$assetGroup])) $assets[$assetGroup] = [];
                                foreach ($assetsGroup as $asset) {
                                    $assets[$assetGroup][] = Utils::shorten($asset);
                                }
                            }
                        }

                        $struct[$path] = [
                            'template'=>$template,
                            'assets'=>$assets,
                            'id'=>$blockId,
                            'exists'=>$options['exists'],
                            'shown'=>$options['shown'],
                            'parentId'=>$parentId,
                        ];

                    });
                    return $struct;
                }
            ]
        ]);

        $this->set('dashboard-options', [
            'options' => [
                'template'=>__DIR__.'/templates/options.php'
            ],
            'data' => [
                'options'=> []
            ]
        ])->updateAssets('dashboard-options', [
        ]);

        $this->set('dashboard-stats', [
            'options' => [
                'template'=>__DIR__.'/templates/stats.php'
            ],
            'data' => [
                'options'=> []
            ]
        ])->updateAssets('dashboard-options', [
        ]);

        $this->layout->beforeRenderAnyBlock(function ($ev, Layout $layout, $id) use($fromClass) {
            $block = $layout->get($id);
            $caller = [];
            if ($block) {
                $caller = Utils::findCaller($block->stack, $fromClass);
            }
            echo "<c_block_node id='$id' caller='".\json_encode($caller)."'>";
        });
        $this->layout->afterRenderAnyBlock(function () use($fromClass) {
            echo "</c_block_node>";
        });
        $this->layout->afterRender(function ($ev, Layout $layout) use($fromClass) {
            $content = $layout->getRoot()->body;
            $layout->getRoot()->body = str_replace(
                "<!-- placeholder layout structure -->",
                $layout->resolve('dashboard-layout-structure')->body,
                $content);
        });

        return $this;
    }
}
