<?php
namespace C\BlogData\Eloquent;

use C\BlogData\EntryRepositoryInterface;
use C\Misc\Utils;
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
            ->insertGetId(Utils::objectToArray($data));
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
     * @param int $page
     * @param int $by
     * @return array|static[]
     */
    public function mostRecent($page=0, $by=5) {
        return $this->capsule->getConnection()
            ->table('blog_entry')
            ->offset($page*$by)
            ->take($by)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    /**
     * @return int
     */
    public function countAll() {
        return $this->capsule->getConnection()
            ->table('blog_entry')
            ->count('id');
    }
}
