<?php

namespace C\Repository;

interface TagableRepositoryInterface {

    /**
     * @param $tager
     * @return mixed
     */
    public function tagable($tager);

    /**
     * @return RepositoryProxy
     */
    public function tager();
}