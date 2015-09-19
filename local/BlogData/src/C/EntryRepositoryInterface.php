<?php
namespace C\BlogData;

use C\Repository\TagableRepositoryInterface;

interface EntryRepositoryInterface extends TagableRepositoryInterface {

    /**
     * @param $tager
     * @return EntryRepositoryInterface
     */
    public function tagable($tager);

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
     * @param int $from
     * @param int $length
     * @return array
     */
    public function mostRecent($from=0, $length=20);
}
