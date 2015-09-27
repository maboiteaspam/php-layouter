<?php

namespace C\Repository;

use C\TagableResource\TagableResourceInterface;
use C\TagableResource\TagedResource;
use C\TagableResource\UnwrapableResourceInterface;

class RepositoryProxy implements TagableResourceInterface, UnwrapableResourceInterface {

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
        $method_name = $this->method[0];
        $method_args = $this->method[1];
        foreach( $method_args as $index=>$arg) {
            if ($arg instanceof UnwrapableResourceInterface) {
                $method_args[$index] = $arg->unwrap();
            }
        }
        return call_user_func_array([$this->repository, $method_name], $method_args);
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
            $method_args = $this->method[1];
            foreach( $method_args as $index=>$arg) {
                if ($arg instanceof TagableResourceInterface) {
                    $res->addTaggedResource($arg->getTaggedResource());
                }
            }
            return $res;
        }
        return $this->tager->getTaggedResource();
    }

    public function __call ($method, $args) {
        $this->method = [$method, $args,];
        return $this;
    }
}