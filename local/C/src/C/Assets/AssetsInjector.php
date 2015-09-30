<?php

namespace C\Assets;

use C\FS\KnownFs;
use C\FS\LocalFs;
use C\Misc\Utils;
use Silex\Application;
use C\Layout\Layout;

class AssetsInjector {

    /**
     * @var string
     */
    public $buildDir;

    /**
     * @var string
     */
    public $wwwDir;
    /**
     * @var KnownFS
     */
    public $assetsFS;
    /**
     * @var bool
     */
    public $concatenate;

    public function getExtFromTarget ($target){
        return strpos($target, 'js')===false?"css":"js";
    }

    public function mergeAllAssets (Layout $layout) {
        $blockAssets = [];
        foreach ($layout->registry->blocks as $block) {
            foreach ($block->assets as $target=>$assets) {
                if (!isset($blockAssets[$target])) {
                    $blockAssets[$target] = [];
                }
                $blockAssets[$target] = array_merge($blockAssets[$target], $assets);
            }
        }
        return $blockAssets;
    }

    public function createBridgedHTMLAssets ($target, $assets) {
        $html = '';
        $assetsFS = $this->assetsFS;
        $ext = $this->getExtFromTarget($target);
        foreach ($assets as $asset) {
            if ($assetsFS->file_exists($asset)) {
                $a = $assetsFS->get($asset);
                if ($a) {
                    $assetName = $a['dir'].$a['name'];
                    $assetUrl = "$assetName?t=".$a['sha1'];

                    if ($ext==="js")
                        $html .= sprintf(
                            '<script src="/%s" type="text/javascript"></script>',
                            str_replace("\\", "/", $assetUrl));
                    else
                        $html .= sprintf(
                            '<link href="/%s" rel="stylesheet" />',
                            str_replace("\\", "/", $assetUrl));

                    $html .= "\n";
                } else {
                    // @todo add log
                    // missing asset
//                    var_dump($assetsFS);
                }
            }
        }
        return $html;
    }


    public $blockToFile = [];

    public function createMergedHTMLAssets ($target, $assets) {
        $html = '';
        $ext = $this->getExtFromTarget($target);
        $assetsFS = $this->assetsFS;
        $buildDir = $this->buildDir;
        $wwwDir = $this->wwwDir;
        if (!LocalFs::is_dir($buildDir)) LocalFs::mkdir($buildDir);
        $basePath = $assetsFS->getBasePath();
        $h = '';
        foreach ($assets as $i=>$asset) {
            if ($assetsFS->file_exists($asset)) {
                $a = $assetsFS->get($asset);
                $h .= $i . '-' . $a['sha1'] . '-';
            }
        }

        // for debug purpose
//        if ($layout->debugEnabled)
            $h = sha1($h.Utils::fileToEtag(__FILE__));
//        else
        $h = sha1($h);

        $concatAssetName = "$target-$h.$ext";

        $this->blockToFile[$target] = "$basePath/$buildDir/$concatAssetName";

        $concatAssetUrl = "$wwwDir/$concatAssetName";

        if ($ext==="js")
            $html .= sprintf(
                '<script src="%s" type="text/javascript"></script>',
                str_replace("\\", "/", $concatAssetUrl));
        else
            $html .= sprintf(
                '<link href="%s" rel="stylesheet" />',
                str_replace("\\", "/", $concatAssetUrl));

        return $html;
    }

    public function applyToLayout (Layout $layout) {
        $allAssets = $this->mergeAllAssets($layout);

        foreach( $allAssets as $target => $assets) {
            $targetBlock = $layout->getOrCreate($target);

            $targetBlock->body .= "\n";

            if ($this->concatenate===false) {
                $targetBlock->body .= $this->createBridgedHTMLAssets($target, $assets);
            } else {
                $targetBlock->body .= $this->createMergedHTMLAssets($target, $assets);
            }

        }
    }

    public function createMergedAssetsFiles (Layout $layout) {
        $blockToFile = $this->blockToFile;
        $blockAssets = $this->mergeAllAssets($layout);
        foreach ($blockAssets as $target => $assets) {
            if (!LocalFs::file_exists($blockToFile[$target])) {
                $filesContent = [];
                foreach ($assets as $asset) {
                    $filesContent[$asset] = $this->readAndMakeAsset($asset);
                }
                if (strpos($target, 'js')!==false) $c = join(";\n", $filesContent) . ";\n";
                else $c = join("\n", $filesContent) . "\n";
                LocalFs::file_put_contents($blockToFile[$target], $c);
            }
        }
    }

    public function readAndMakeAsset ($assetFile){
        $assetsFS = $this->assetsFS;
        $assetItem  = $assetsFS->get($assetFile);
        if ($assetItem) {
            $assetPath  = $assetItem['absolute_path'];
            $content    = LocalFs::file_get_contents($assetPath);
            $assetShortPath  = $assetItem['dir'].$assetItem['name'];
            if ($assetItem['extension']==='css') {
                $matches = [];
                preg_match_all('/url\s*\(([^)]+)\)/i', $content, $matches);
                foreach($matches[1] as $i=>$match){
                    if (substr($match,0,1)==='"' || substr($match,0,1)==="'") {
                        $match = substr($match, 1, -1);
                    }
                    $content = str_replace($matches[0][$i], "url(/".$assetItem['dir']."/$match)", $content);
                }
                $content = "/* $assetFile -> $assetShortPath */ \n$content";
            } else if ($assetItem['extension']==='js') {
                $content = "(function(modulePath){;".$content.";})('".$assetItem['dir']."');";
            }
        } else {
            $content = "\n/* assset not found $assetFile */\n";
        }

        return $content;
    }


}