<?php
namespace C\StaticLayoutBuilder;

use C\LayoutBuilder\Transforms as BaseTransforms;
use C\FS\LocalFs;
use Symfony\Component\Yaml\Yaml;

class Transforms extends BaseTransforms{

    /**
     * @param mixed $app
     * @return Transforms
     */
    public static function transform($app) {
        return new Transforms($app);
    }

    public function resolveFilePath ($baseDir, $fileToResolve) {
        if (strpos($fileToResolve, ":")!==false) { // it s using some sort of module path : MyBlog:path/to/some/file.ext
            // to be resolved later.
        } else if (substr($fileToResolve, 0, 1)==="/"
            || preg_match("/[a-z]:[\\\]/i", substr($fileToResolve, 0, 3))>0) { // it s an absolute path, pass.
            // nothing to do in current impl.
        } else {
            // let s assume their are relative to the path of the loaded YML file.
            $fileToResolve = "$baseDir/$fileToResolve";
        }
        return $fileToResolve;
        // see also
        // http://stackoverflow.com/questions/9990961/how-do-i-get-a-list-of-bundles-in-symfony2
        // http://stackoverflow.com/questions/7585474/accessing-files-relative-to-bundle-in-symfony2
        // https://github.com/symfony/symfony/tree/master/src/Symfony/Component/HttpKernel
        // https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpKernel/Kernel.php
        // http://symfony.com/doc/current/components/finder.html
    }

    public function loadFile ($filePath) {
        $ymlContent     = LocalFs::file_get_contents ($filePath);
        $layoutStruct   = Yaml::parse ($ymlContent, true, false, true);
        $ymlDir         = dirname ($filePath);
        // search paths, if they are relative, make them absolute.
        foreach ($layoutStruct["structure"] as $blockId => $block) {
            if (isset($block["set_template"])) {
                $block["set_template"] = $this->resolveFilePath($ymlDir, $block["set_template"]);
            }
            if (isset($block["add_assets"])) {
                foreach ($block["add_assets"] as $targetAssetsBlock => $assets) {
                    foreach ( $assets as $i => $asset) {
                        $assets[$i] = $this->resolveFilePath($ymlDir, $asset);
                    }
                }
            }
            if (isset($block["insert_before"])) {
            }
        }
        // Definitely apply the static-file-layout to the Layout instance.
        foreach ($layoutStruct["structure"] as $blockId => $block) {
            if (isset($block["set_template"])) {
                $this->setTemplate($blockId, $block["set_template"] );
            }
            if (isset($block["add_assets"])) {
                $this->updateAssets($blockId, $block["add_assets"] );
            }
            if (isset($block["insert_before"])) {
                $this->insertBefore($block["insert_before"], $blockId, []);
            }
        }
        return $this;
    }

}
