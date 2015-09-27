<?php
namespace C\ModernApp\HTML;

use C\FS\KnownFs;
use C\Layout\Transforms as BaseTransforms;
use C\Misc\Utils;
use C\FS\LocalFs;
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

    public function baseTemplate () {
        $this->setTemplate('root',
            'HTML:/html.php'
        )->set('html_begin', [
            'body'=>'<html>'
        ])->setTemplate('head',
            'HTML:/head.php'
        )->set('body', [
            'options'=>[
                'template'=> 'HTML:/1-column.php'
            ],
        ])->setTemplate('footer',
            'HTML:/footer.php'
        )->set('script_bottom',  [
            'body'=>''
        ])->set('html_end',[
            'body'=>'</html>'
        ]);
        return $this;
    }

    public function applyAssets (Application $app){

        $documentRoot = $app['documentRoot'];
        $assetsFS = $app['assets.fs'];
        $basePath = $assetsFS->getBasePath();
        $concat = $app['assets.concat'];
        $layout = $this->layout;

        $this->layout->beforeRender(function () use(&$app, &$assetsFS, &$layout, $basePath, $concat, $documentRoot) {

            $blockAssets = [];
            $blockToFile = [];
            foreach ($this->layout->registry->blocks as $block) {
                foreach ($block->assets as $target=>$assets) {
                    if (!isset($blockAssets[$target])) {
                        $blockAssets[$target] = [];
                    }
                    $blockAssets[$target] = array_merge($blockAssets[$target], $assets);
                }
            }

            if (count($blockAssets)) {

                foreach ($blockAssets as $target => $assets) {
                    $targetBlock = $this->layout->getOrCreate($target);
                    if ($targetBlock) {
                        $ext = strpos($target, 'js')===false?"css":"js";

                        $targetBlock->body .= "\n";

                        if ($concat) {

                            $h = '';
                            foreach ($assets as $i=>$asset) {
                                if ($assetsFS->file_exists($asset)) {
                                    $a = $assetsFS->get($asset);
                                    $h .= $i . '-' . $a['sha1'] . '-';
                                }
                            }

                            if ($layout->debugEnabled) $h = sha1($h.Utils::fileToEtag(__FILE__));
                            else $h = sha1($h);

                            $concatAssetName = "$target-$h.$ext";
                            $blockToFile[$target] = $basePath . $concatAssetName;
                            $concatAssetUrl = substr($basePath, strlen($documentRoot)) . $concatAssetName;

                            if ($ext==="js")
                                $targetBlock->body .= sprintf(
                                    '<script src="/%s" type="text/javascript"></script>',
                                    str_replace("\\", "/", $concatAssetUrl));
                            else
                                $targetBlock->body .= sprintf(
                                    '<link href="/%s" rel="stylesheet" />',
                                    str_replace("\\", "/", $concatAssetUrl));

                        } else {
                            foreach ($assets as $asset) {
                                if ($assetsFS->file_exists($asset)) {
                                    $a = $assetsFS->get($asset);
                                    if ($a) {
                                        $assetName = $a['dir'].$a['name'];
                                        $assetUrl = "$assetName?t=".$a['sha1'];

                                        if ($ext==="js")
                                            $targetBlock->body .= sprintf(
                                                '<script src="/%s" type="text/javascript"></script>',
                                                str_replace("\\", "/", $assetUrl));
                                        else
                                            $targetBlock->body .= sprintf(
                                                '<link href="/%s" rel="stylesheet" />',
                                                str_replace("\\", "/", $assetUrl));

                                        $targetBlock->body .= "\n";
                                    } else {
                                        var_dump($assetsFS);
                                    }
                                }
                            }
                        }
                    }
                }

                if ($concat) {
                    $app->after(function()use(&$assetsFS, &$blockAssets, &$blockToFile){
                        foreach ($blockAssets as $target => $assets) {
                            if (!LocalFs::file_exists($blockToFile[$target])) {
                                $filesContent = [];
                                foreach ($assets as $asset) {
                                    $filesContent[$asset] = $this->readAndMakeAsset($assetsFS, $asset);
                                }
                                if (strpos($target, 'js')!==false) $c = join(";\n", $filesContent) . ";\n";
                                else $c = join("\n", $filesContent) . "\n";
                                LocalFs::file_put_contents($blockToFile[$target], $c);
                            }
                        }
                    }, Application::LATE_EVENT);
                }
            }
        });

        return $this;
    }

    public function readAndMakeAsset (KnownFs $assetsFS, $assetFile){
        if ($assetsFS->file_exists($assetFile)) {
            $content    = LocalFs::file_get_contents($assetFile);
            $assetFile  = $assetsFS->realpath($assetFile);
            $assetItem  = $assetsFS->get($assetFile);
            $assetFile  = $assetItem['dir'].$assetItem['name'];
            if ($assetItem['extension']==='css') {
                $matches = [];
                preg_match_all('/url\s*\(([^)]+)\)/i', $content, $matches);
                foreach($matches[1] as $i=>$match){
                    if (substr($match,0,1)==='"' || substr($match,0,1)==="'") {
                        $match = substr($match, 1, -1);
                    }
                    $content = str_replace($matches[0][$i], "url(/".$assetItem['dir']."/$match)", $content);
                }
                $content = "/* $assetFile */ \n$content";
            } else if ($assetItem['extension']==='js') {
                $content = "(function(modulePath){;".$content.";})('".$assetItem['dir']."');";
            }
        } else {
            $content = "\n/* assset not found $assetFile */\n";
        }

        return $content;
    }




    public function finalize (Application $app) {
        $this->applyAssets($app);
        return $this;
    }
}
