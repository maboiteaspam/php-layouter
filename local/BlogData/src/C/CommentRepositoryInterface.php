<?php
namespace C\BlogData;

use C\Repository\TagableRepositoryInterface;

interface CommentRepositoryInterface extends TagableRepositoryInterface{

    /**
     * @param $tager
     * @return CommentRepositoryInterface
     */
    public function tagable($tager=null);

    /**
     * @return CommentRepositoryInterface
     */
    public function tager();


    /**
     * @return string
     */
    public function lastUpdateDate();

    /**
     * @param $id
     * @return string
     */
    public function lastUpdatedByEntryId($id);

    /**
     * @param $data
     * @return int
     */
    public function insert($data);

    /**
     * @param $id
     * @param int $from
     * @param int $length
     * @return array
     */
    public function byEntryId($id, $from=0, $length=5);

    /**
     * @param array $excludesEntries
     * @param int $page
     * @param int $by
     * @return array
     */
    public function mostRecent($excludesEntries=[], $page=0, $by=20);
}
