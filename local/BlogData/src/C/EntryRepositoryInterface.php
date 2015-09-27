<?php
namespace C\BlogData;

use C\Repository\TagableRepositoryInterface;

interface EntryRepositoryInterface extends TagableRepositoryInterface {

    /**
     * @param $tager
     * @return EntryRepositoryInterface
     */
    public function tagable($tager=null);

    /**
     * @return EntryRepositoryInterface
     */
    public function tager();

    /**
     * @return string
     */
    public function lastUpdateDate();

    /**
     * @param $data
     * @return int
     */
    public function insert($data);

    /**
     * @param $id
     * @return array
     */
    public function byId($id);

    /**
     * @param int $page
     * @param int $by
     * @return array
     */
    public function mostRecent($page=0, $by=20);

    /**
     * @return array
     */
    public function countAll();
}
