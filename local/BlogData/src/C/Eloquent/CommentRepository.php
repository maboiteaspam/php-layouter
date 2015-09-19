<?php
namespace C\BlogData\Eloquent;

use C\BlogData\CommentRepositoryInterface;
use C\Repository\TagableEloquentRepository;

class CommentRepository extends TagableEloquentRepository implements CommentRepositoryInterface {

    /**
     * @return string
     */
    public function lastUpdateDate() {
        return $this->capsule->getConnection()
            ->table('blog_comment')
            ->take(1)
            ->orderBy('updated_at','DESC')
            ->first(['updated_at']);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function lastUpdatedByEntryId($id) {
        return $this->byEntryId($id)[0]->updated_at;
    }

    /**
     * @param array $data
     * @return int
     */
    public function insert($data) {
        return $this->capsule->getConnection()
            ->table('blog_comment')
            ->insertGetId($data);
    }

    /**
     * @param $id
     * @param int $from
     * @param int $length
     * @return array|static[]
     */
    public function byEntryId($id, $from=0, $length=5) {
        return $this->capsule->getConnection()
            ->table('blog_comment')
            ->where('blog_entry_id', '=', $id)
            ->take($length)
            ->offset($from)
            ->orderBy('updated_at','DESC')
            ->get();
    }

    /**
     * @param array $excludesEntries
     * @param int $from
     * @param int $length
     * @return array|static[]
     */
    public function mostRecent($excludesEntries=[], $from=0, $length=5) {
        return $this->capsule->getConnection()
            ->table('blog_comment')
            ->whereNotIn('blog_entry_id', $excludesEntries)
            ->take($length)
            ->offset($from)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }
}
