<?php
namespace C\ModernApp\Dashboard;

use C\Layout\Transforms as Base;
use C\Layout\Layout;
use C\Misc\Utils;

class Transforms extends Base{

    /**
     * @param mixed $options
     * @return Transforms
     */
    public static function transform($options){
        return new self($options);
    }

    /**
     * @param string $fromClass
     * @return \C\Layout\Transforms
     */
    public function show ($fromClass=''){
        $this->insertBeforeBlock('html_end', 'dashboard', [
            'options' => [
                'template'=>'Dashboard:/dashboard.php'
            ]
        ])->addAssets('dashboard', [
            'template_head_css'=>[
                'Dashboard:/dashboard.css'
            ],
            'page_footer_js'=>[
                'Dashboard:/dashboard.js'
            ],
        ]);

        $this->set('dashboard-layout', [
            'body' => "<!-- layout_structure_placeholder -->",
        ])->addAssets('dashboard-layout', [
            'template_head_css'=>[
                'Dashboard:/layout-structure.css'
            ],
            'page_footer_js'=>[
                'Dashboard:/layout-structure.js'
            ],
        ]);

        $this->set('dashboard-options', [
            'options' => [
                'template'=>'Dashboard:/options.php'
            ],
            'data' => [
                'options'=> []
            ]
        ])->addAssets('dashboard-options', [
        ]);

        $this->set('dashboard-stats', [
            'options' => [
                'template'=>'Dashboard:/stats.php'
            ],
            'data' => [
                'options'=> []
            ]
        ])->addAssets('dashboard-options', [
        ]);

        $this->layout->beforeRenderAnyBlock(function ($ev, Layout $layout, $id) use($fromClass) {
            $block = $layout->get($id);
            if ($block) {
                $caller = Utils::findCaller($block->stack, $fromClass);
                $block->body = "\n<c_block_node id='$id' caller='".\json_encode($caller)."'\n>".$block->body;
            }
        });
        $this->layout->afterRenderAnyBlock(function ($ev, Layout $layout, $id) {
            $block = $layout->get($id);
            if ($block) {
                $block->body = $block->body."\n</c_block_node>\n";
            }
        });


        if ($this->layout->serializer) {
            $serializer = $this->layout->serializer;

            // this is a special case.
            // the block needs to be generated after ALL blocks,
            // then re injected into the document.
            $this->layout->afterRender(function ($ev, Layout $layout) use($serializer) {
                $rootBlock = $layout->getRoot();

                if ($rootBlock) {
                    $content = $rootBlock->body;

                    $this->set('dashboard-layout-structure', [
                        'options' => [
                            'template'=>'Dashboard:/layout-structure.php'
                        ],
                        'data' => [
                            'serialized'=> $serializer->serialize($layout)
                        ]
                    ]);

                    $rootBlock->body = str_replace(
                        "<!-- layout_structure_placeholder -->",
                        $layout->resolve('dashboard-layout-structure')->body,
                        $content);
                }
            });
        }

        return $this;
    }
}
