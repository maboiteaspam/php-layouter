<?php

namespace C\Intl;

interface IntlLoaderInterface {

    public function isExt ($ext);

    public function clearCache ();

    public function loadFromCache ($file);

    public function saveToCache ($file);

    public function load ($file);

}