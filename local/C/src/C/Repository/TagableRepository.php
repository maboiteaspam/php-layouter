<?php

namespace C\Repository;

abstract class TagableRepository implements TagableRepositoryInterface, RepositoryInterface{

    /**
     * @param $tager
     * @return RepositoryProxy
     */
    public function tagable ($tager=null) {
        $taged = new RepositoryProxy($this);
        if ($tager) $taged->setTager($tager);
        return $taged;
    }
    /**
     * @return RepositoryProxy
     */
    public function tager() {
        $tager = new RepositoryProxy($this);
        return $tager;
    }
    public $repositoryName;
    public function setRepositoryName ($name) {
        $this->repositoryName = $name;
    }
    public function getRepositoryName () {
        return $this->repositoryName;
    }
}