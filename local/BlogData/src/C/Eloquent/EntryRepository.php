<?php
namespace C\BlogData\Eloquent;

use C\BlogData\EntryRepositoryInterface;
use C\Repository\TagableEloquentRepository;

class EntryRepository extends TagableEloquentRepository implements EntryRepositoryInterface {

    /**
     * @return array
     */
    public function lastUpdateDate() {
        return $this->capsule->getConnection()
            ->table('blog_entry')
            ->take(1)
            ->orderBy('updated_at','DESC')
            ->first(['updated_at']);
    }

    /**
     * @param array $data
     * @return int
     */
    public function insert($data) {
        return $this->capsule->getConnection()
            ->table('blog_entry')
            ->insertGetId($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function byId($id) {
        return $this->capsule->getConnection()
            ->table('blog_entry')
            ->where('id', '=', $id)
            ->take(1)
            ->first();
    }

    /**
     * @param int $from
     * @param int $length
     * @return array|static[]
     */
    public function mostRecent($from=0, $length=20) {
        return $this->capsule->getConnection()
            ->table('blog_entry')
            ->offset($from)
            ->take($length)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }
}
