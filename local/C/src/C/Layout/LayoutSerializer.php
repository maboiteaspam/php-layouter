<?php

namespace C\Layout;

use C\FS\KnownFs;
use C\Misc\Utils;
use Silex\Application;

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
    /**
     * @var Application
     */
    public $app;

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
    public function setApp (Application $app) {
        $this->app = $app;
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

        $app = $this->app;
        $root = $layout->get($layout->block);
        $layout->traverseBlocksWithStructure($root, $layout, function ($blockId, $parentId, $path, $options) use($app, &$blocks, $modernFS, $layoutFS, $assetsFS, $intlFS) {
            $block = $options['block'];
            /* @var $block Block */
            $template = 'inlined body';
            $templateFile = '';
            $assets = [];
            $data = [];
            $isCacheable = true;

            if ($block) {
                if (isset($block->options['template'])) {
                    $template = $block->options['template'];
                    $templateFile = $layoutFS->get($block->options['template']);
                    $templateFile = Utils::shorten($templateFile['absolute_path']);
                }
                if ($this->assetsFS) {
                    foreach ($block->assets as $assetGroup=>$assetsGroup) {
                        if (!isset($assets[$assetGroup])) $assets[$assetGroup] = [];
                        foreach ($assetsGroup as $asset) {
                            $item = $this->assetsFS->get($asset);
                            $assets[$assetGroup][] = [
                                'name'=>$asset,
                                'path'=> $item?$item['dir'].$item['name']:'not found'
                            ];
                        }
                    }
                }

                $blockTags = null;
                try{
                    $blockTags = $block->getTaggedResource();
                }catch(\Exception $ex){

                }
                $unWrapped = $block->unwrapData();

                foreach( $unWrapped as $k=>$v) {
                    $tags = !$blockTags?[]:$blockTags->getResourcesByName($k);
                    $tagsClear = [];
                    foreach ($tags as $tag) {
                        $t = [
                            'type'=>$tag['type'],
                            'value'=>''
                        ];
                        if ($tag['type']==='repository') {
                            $t['value'] = $tag['value'][0]."->".$tag['value'][1][0];
                            $t['type'] = get_class($app[$tag['value'][0]]);
                        } else if ($tag['type']==='asset' || $tag['type']==='modern.layout') {
                            // @todo to complete, check tagDataWith('asset'...
//                            var_dump($tag);
//                            $t['file'] = $tag['value'][0]."->".$tag['value'][1][0];
                        } else if ($tag['type']==='sql') {
                            // @todo to complete, check tagDataWith('sql'...
//                            var_dump($tag);
                        } else if ($tag['type']==='po') {
                            $t['value'] = var_export($tag['value'], true);
                        }
                        $tagsClear[] = $t;
                    }
                    $data[] = [
                        'name' =>$k,
                        'tags' => $tagsClear,
                        'value' => is_object($v)? get_class($v):
                            is_array($v) ? "Array(".gettype($v).")[".count($v)."]" : var_export($v, true)
                            ,
                    ];
                }

                try{
                    serialize($unWrapped);
                }catch(\Exception $ex){
                    $isCacheable = false;
                }
            }

            $blocks[$path] = [
                'template'=>$template,
                'templateFile'=>$templateFile,
                'assets'=>$assets,
                'id'=>$blockId,
                'data'=>$data,
                'exists'=>$options['exists'],
                'shown'=>$options['shown'],
                'isCacheable'=>$isCacheable,
                'parentId'=>$parentId,
            ];
        });

        $serialized['blocks'] = $blocks;

        return $serialized;
    }
}
