<?php

namespace C\Repository;

use C\TagableResource\TagableResourceInterface;
use C\TagableResource\TagedResource;

class RepositoryProxy implements TagableResourceInterface {

    /**
     * @var RepositoryInterface
     */
    public $repository;
    /**
     * @var array
     */
    public $method;
    /**
     * @var \C\TagableResource\TagableResourceInterface
     */
    public $tager;

    public function __construct (RepositoryInterface $repository) {
        $this->repository = $repository;
        $this->proxied = null;
        $this->tager = null;
    }

    /**
     * @return mixed
     */
    public function unwrap() {
        return call_user_func_array([$this->repository, $this->method[0]], $this->method[1]);
    }

    /**
     * @param RepositoryProxy $tager
     */
    public function setTager(RepositoryProxy $tager) {
        $this->tager = $tager;
    }

    /**
     * @return TagedResource
     */
    public function getTaggedResource() {
        if (!$this->tager) {
            $res = new TagedResource();
            $res->addResource([$this->repository->getRepositoryName(), $this->method], 'repository');
            return $res;
        }
        return $this->tager->getTaggedResource();
    }

    public function __call ($method, $args) {
        $this->method = [$method, $args,];
        return $this;
    }
}