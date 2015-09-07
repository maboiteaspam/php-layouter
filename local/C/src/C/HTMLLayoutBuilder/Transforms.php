<?php
namespace C\HTMLLayoutBuilder;

use C\LayoutBuilder\Transforms as BaseTransforms;
use C\Misc\Utils;
use C\LayoutBuilder\Layout\Layout;

class Transforms extends BaseTransforms{

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout) {
        return new Transforms($layout);
    }

    public function createBase($options=[]){
        $options = array_merge([
            'options'=>[
                'template'=> __DIR__ . '/templates/1-column.php'
            ],
        ], $options);
        $this->layout->setRoot([
            'options'=> [
                'template'=>__DIR__.'/templates/html.php',
            ],
        ]);
        $this->set('html_begin', ['body'=>'<html>']);
        $this->set('head', [
            'options'=> [
                'template' => __DIR__.'/templates/head.php',
            ],
        ]);
        $this->set('body', $options);
        $this->set('footer', [
            'options'=> [
                'template' => __DIR__.'/templates/footer.php',
            ],
        ]);
        $this->set('script_bottom', ['body'=>'']);
        $this->set('html_end', ['body'=>'</html>']);
        return $this;
    }

    public function applyAssets($options=[]){
        $options = array_merge([
            'projectPath'   => getcwd(),
            'documentRoot'  => 'www/',
            'basePath'      => 'assets/',
            'concat'        => true
        ], $options);

        $files = [];
        foreach ($this->layout->registry->blocks as $block) {
            foreach ($block->assets as $target=>$assets) {
                if (!isset($files[$target])) {
                    $files[$target] = [];
                }
                $files[$target] = array_merge($files[$target], $assets);
            }
        }

        if (count($files)>0) {
            $documentRoot = $options['documentRoot'];
            $basePath = $options['basePath'];
            $assetsPath = $documentRoot.$basePath;

            if (!is_dir($assetsPath)) mkdir($assetsPath, 0700, true);

            foreach ($files as $target=>$assets) {
                $targetBlock = $this->layout->getOrCreate($target);
                $filesContent = [];

                if ($targetBlock) {
                    preg_match("/(css|js)$/", $target, $matches);
                    $ext = $matches[1];

                    $targetBlock->body .= "\n";

                    if ($options['concat']===true) {

                        $h = Utils::fileToEtag(array_merge($assets, [__FILE__]));

                        $concatAssetName = $target.'-'.sha1($h).'.'.$ext;
                        $concatAssetFile = $assetsPath . $concatAssetName;
                        $concatAssetUrl = $basePath . $concatAssetName;

                        if (!file_exists($concatAssetFile)) {
                            foreach ($assets as $asset) {
                                $filesContent[$asset] = file_get_contents($asset);
                            }
                            if ($ext==='js') $c = ";\n" . join(";\n", $filesContent) . "\n";
                            else $c = "" . join("\n", $filesContent) . "\n";
                            file_put_contents($concatAssetFile, $c);
                        }

                        if ($ext==='js')
                            $targetBlock->body .= sprintf(
                                '<script src="/%s" type="text/javascript"></script>', $concatAssetUrl);
                        else
                            $targetBlock->body .= sprintf(
                                '<link href="/%s" rel="stylesheet" />', $concatAssetUrl);

                    } else {

                        foreach ($assets as $asset) {
                            $t = Utils::relativePath($asset, $options['projectPath']);
                            if (substr($t,0,2)==='./') $t = substr($t,2);
                            $assetName = str_replace('/', '_', $t);
                            $assetFile = $assetsPath . $assetName;
                            $assetUrl = $basePath . $assetName;

                            if (!file_exists($assetFile)) {
                                if (!is_dir(dirname($assetFile))) mkdir($assetFile, 0777, true);
                                copy($asset, $assetFile);
                            } else if (filemtime($assetFile)!==filemtime($asset)) {
                                copy($asset, $assetFile);
                            }

                            if ($ext==='js')
                                $targetBlock->body .= sprintf(
                                    '<script src="/%s" type="text/javascript"></script>', $assetUrl);
                            else
                                $targetBlock->body .= sprintf(
                                    '<link href="/%s" rel="stylesheet" />', $assetUrl);

                            $targetBlock->body .= "\n";
                        }

                    }

                }
            }
        }
        return $this;
    }
}
