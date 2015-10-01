<?php

namespace C\Watch;

interface WatchedInterface {

    public function setName ($name);
    public function getName ();

    public function clearCache ();

    public function resolveRuntime ();

    /**
     * @return WatchedInterface
     */
    public function build ();

    /**
     * @return bool
     */
    public function loadFromCache ();

    /**
     * @return mixed
     */
    public function dump ();

    /**
     * @return mixed
     */
    public function saveToCache ();

    /**
     * @param $action
     * @param $file
     * @return mixed
     */
    public function changed ($action, $file);
}