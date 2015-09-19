<?php

namespace C\Assets;

use C\FS\LocalFs;
use C\FS\KnownFs;

class Bridger {

    public function generate ($file, $type, KnownFs $fs) {
        $projectPath = $fs->registry->config['basePath'];
        $paths = array_unique($fs->registry->config['paths']);
        $aliases = [];
        if ($type==='builtin') {
            foreach ($paths as $i=>$path) {
                $urlAlias = substr(realpath($path), strlen(realpath($projectPath)));
                $urlAlias = str_replace(DIRECTORY_SEPARATOR, "/", $urlAlias);
                $aliases[$urlAlias] = realpath($path);
            }
            $aliases = "<?php return ".var_export($aliases, true).";\n";
        } else if ($type==='apache') {
            $aliases = "";
            foreach ($paths as $path) {
                $urlAlias = substr(realpath($path), strlen(realpath($projectPath))+1);
                $urlAlias = str_replace(DIRECTORY_SEPARATOR, "/", $urlAlias);
                $aliases .= "Alias $urlAlias\t$path\n";
            }
        } else if ($type==='nginx') {
            $aliases = "";
            foreach ($paths as $path) {
                $urlAlias = substr(realpath($path), strlen(realpath($projectPath))+1);
                $urlAlias = str_replace(DIRECTORY_SEPARATOR, "/", $urlAlias);
                $aliases .= "Alias $urlAlias\t$path\n";
            }
        }
        return LocalFs::file_put_contents($file, $aliases);
    }

}
