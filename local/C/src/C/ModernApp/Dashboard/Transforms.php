<?php
namespace C\ModernApp\Dashboard;

use C\Layout\Transforms as Base;
use C\Layout\Layout;
use C\Misc\Utils;

class Transforms extends Base{

    /**
     * @param string $fromClass
     * @return \C\Layout\Transforms
     */
    public function show ($fromClass=''){

        /* @var $layout \C\Layout\Layout */
        $layout = $this->layout;

        $this->insertBeforeBlock('html_end', 'dashboard', [
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
            'body' => "<!-- layout_structure_placeholder -->",
        ])->updateAssets('dashboard-layout', [
            'template_head_css'=>[
                __DIR__ . '/assets/layout-structure.css'
            ],
            'page_footer_js'=>[
                __DIR__ . '/assets/layout-structure.js'
            ],
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
            if ($block) {
                $caller = Utils::findCaller($block->stack, $fromClass);
                $block->body = "<c_block_node id='$id' caller='".\json_encode($caller)."'>".$block->body;
            }
        });
        $this->layout->afterRenderAnyBlock(function ($ev, Layout $layout, $id) {
            $block = $layout->get($id);
            if ($block) {
                $block->body = $block->body."</c_block_node>";
            }
        });


        $structGen = function () use($layout) {
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
        };
        $this->layout->afterRender(function ($ev, Layout $layout) use(&$structGen) {
            $content = $layout->getRoot()->body;

            $this->set('dashboard-layout-structure', [
                'options' => [
                    'template'=>__DIR__.'/templates/layout-structure.php'
                ],
                'data' => [
                    'struct'=> $structGen
                ]
            ]);

            $layout->getRoot()->body = str_replace(
                "<!-- layout_structure_placeholder -->",
                $layout->resolve('dashboard-layout-structure')->body,
                $content);
        });

        return $this;
    }
}
