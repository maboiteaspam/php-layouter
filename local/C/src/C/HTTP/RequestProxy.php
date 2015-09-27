<?php

namespace C\HTTP;

use C\TagableResource\TagableResourceInterface;
use C\TagableResource\TagedResource;
use C\TagableResource\UnwrapableResourceInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestProxy implements TagableResourceInterface, UnwrapableResourceInterface {

    /**
     * @var Request
     */
    public $request;

    /**
     * @var string
     */
    public $repository;
    /**
     * @var string
     */
    public $param;

    public function __construct ( Request $request ) {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function unwrap() {
        if ($this->repository==='_GET') {
            return $this->request->query->get($this->param, null, true);

        } else if ($this->repository==='_POST') {
            return $this->request->request->get($this->param, null, true);

        } else if ($this->repository==='_COOKIE') {
            return $this->request->cookies->get($this->param, null, true);

        } else if ($this->repository==='_SESSION') {
            return $this->request->getSession()->get($this->param, null, true);

        } else if ($this->repository==='_FILES') {
            return $this->request->files->get($this->param);

        } else if ($this->repository==='_HEADER') {
            return $this->request->headers->get($this->param);

        }
        return false;
    }


    /**
     * @return TagedResource
     */
    public function getTaggedResource() {
        $res = new TagedResource();
        $res->addResource([$this->repository, $this->param], 'request');
        return $res;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function get ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_GET';
        $proxy->param = $param;
        return $proxy;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function post ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_POST';
        $proxy->param = $param;
        return $proxy;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function file ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_FILES';
        $proxy->param = $param;
        return $proxy;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function cookie ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_COOKIE';
        $proxy->param = $param;
        return $proxy;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function session ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_SESSION';
        $proxy->param = $param;
        return $proxy;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function header ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_HEADER';
        $proxy->param = $param;
        return $proxy;
    }
}