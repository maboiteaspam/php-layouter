<?php

namespace C\Layout;

use C\FS\KnownFs;
use C\Misc\Utils;

class LayoutSerializer {

    /**
     * @var KnownFS
     */
    public $modernFS;
    /**
     * @var KnownFS
     */
    public $assetsFS;
    /**
     * @var KnownFS
     */
    public $layoutFS;
    /**
     * @var KnownFS
     */
    public $intlFS;

    public function setLayoutFS (KnownFs $layoutFS) {
        $this->layoutFS = $layoutFS;
    }
    public function setModernFS (KnownFs $modernFS) {
        $this->modernFS = $modernFS;
    }
    public function setAssetsFS (KnownFs $assetsFS) {
        $this->assetsFS = $assetsFS;
    }
    public function setIntlFS (KnownFs $intlFS) {
        $this->intlFS = $intlFS;
    }

    public function serialize (Layout $layout) {
        //-
        $serialized = [
            'layout'=>[],
            'blocks'=>[],
        ];

        // @todo add layout meta information (id, description, and injected modern layouts)
        // @todo add intl

        $layoutFS   = $this->layoutFS;
        $assetsFS   = $this->assetsFS;
        $intlFS     = $this->intlFS;
        $modernFS   = $this->modernFS;

        $blocks = [];

        $root = $layout->get($layout->block);
        $layout->traverseBlocksWithStructure($root, $layout, function ($blockId, $parentId, $path, $options) use(&$blocks, $modernFS, $layoutFS, $assetsFS, $intlFS) {
            $block = $options['block'];
            $template = 'inlined body';
            $templateFile = '';
            $assets = [];
            $assetsFile = [];
            $data = [];

            if ($block) {
                if (isset($block->options['template'])) {
                    $template = $block->options['template'];
                    $templateFile = $layoutFS->get($block->options['template']);
                    $templateFile = Utils::shorten($templateFile['absolute_path']);
                }
                foreach ($block->assets as $assetGroup=>$assetsGroup) {
                    if (!isset($assetsFile[$assetGroup])) $assetsFile[$assetGroup] = [];
                    foreach ($assetsGroup as $asset) {
                        $assetsFile[$assetGroup][] = Utils::shorten($asset);
                    }
                }
                $data = $block->data;
            }

            $blocks[$path] = [
                'template'=>$template,
                'templateFile'=>$templateFile,
                'assets'=>$assets,
                'assetsFile'=>$assetsFile,
                'id'=>$blockId,
                'data'=>$data,
                'exists'=>$options['exists'],
                'shown'=>$options['shown'],
                'parentId'=>$parentId,
            ];
        });

        $serialized['blocks'] = $blocks;

        return $serialized;
    }
}