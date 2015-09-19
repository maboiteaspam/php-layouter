<?php

namespace C\Repository;

interface RepositoryInterface {

    /**
     * @return string
     */
    public function getRepositoryName();

    /**
     * @param $name
     */
    public function setRepositoryName($name);
}
