<?php

namespace C\Intl;

use C\FS\LocalFs;
use Silex\Application;
use Symfony\Component\Yaml\Yaml;

class YmlIntlLoader extends AbstractIntlLoader {

    public function load ($filePath) {
        return Yaml::parse (LocalFs::file_get_contents ($filePath), true, false, true);
    }
}