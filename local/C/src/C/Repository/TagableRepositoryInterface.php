<?php

namespace C\Repository;

interface TagableRepositoryInterface {

    /**
     * @param $tager
     * @return RepositoryProxy
     */
    public function tagable($tager);

    /**
     * @return RepositoryProxy
     */
    public function tager();
}