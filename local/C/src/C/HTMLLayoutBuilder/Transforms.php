<?php
namespace C\HTMLLayoutBuilder;

use C\LayoutBuilder\Layout\Layout;
use C\HTMLLayoutBuilder\Layout\Builder as Builder;
use C\Misc\Utils;

class Transforms{

    public static function createBase($options=[]){
        $options = array_merge([
            'options'=>[
                'template'=> __DIR__ . '/templates/1-column.php'
            ],
        ], $options);
        return function(Layout $layout) use($options){
            Builder::setRoot($layout, [
                'options'=> [
                    'template'=>__DIR__.'/templates/html.php',
                ],
            ]);
            Builder::set($layout, 'html_begin', ['body'=>'<html>']);
            Builder::set($layout, 'head', [
                'options'=> [
                    'template' => __DIR__.'/templates/head.php',
                ],
            ]);
            Builder::set($layout, 'body', $options);
            Builder::set($layout, 'footer', [
                'options'=> [
                    'template' => __DIR__.'/templates/footer.php',
                ],
            ]);
            Builder::set($layout, 'script_bottom', ['body'=>'']);
            Builder::set($layout, 'html_end', ['body'=>'</html>']);
        };
    }

    public static function applyAssets($options=[]){
        $options = array_merge(['documentRoot'=>'www/', 'basePath'=>'assets/'], $options);

        return function(Layout $layout) use($options){
            $files = [];
            foreach ($layout->registry->blocks as $block) {
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

                foreach ($files as $target=>$assets) {
                    $targetBlock = Builder::getOrCreate($layout, $target);

                    if ($targetBlock) {
                        preg_match("/(css|js)$/", $target, $matches);
                        $ext = $matches[1];
                        $h = Utils::fileToEtag(array_merge($assets, [__FILE__]));

                        $assetName = $target.'-'.sha1($h).'.'.$ext;
                        $assetFile = $assetsPath . $assetName;
                        $assetUrl = $basePath . $assetName;

                        if (!file_exists($assetFile)) {
                            $c = '';
                            foreach ($assets as $asset) {
                                $fc = file_get_contents($asset);
                                if ($ext==='js') $fc = ';'.$fc.';';
                                $c .= $fc."\n";
                            }
                            if (!is_dir($assetsPath)) mkdir($assetsPath, 0700, true);
                            file_put_contents($assetFile, $c);
                        }

                        $targetBlock->body .= "\n";
                        if ($ext==='js')
                            $targetBlock->body .= sprintf(
                                '<script src="/%s" type="text/javascript"></script>', $assetUrl);
                        else
                            $targetBlock->body .= sprintf(
                                '<link href="/%s" rel="stylesheet" />', $assetUrl);

                    }
                }
            }
        };
    }
}
