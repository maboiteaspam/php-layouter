<?php

namespace C\BlogData\Eloquent;

use Illuminate\Database\Capsule\Manager as Capsule;

class Comment{
    /**
     * @param array $data
     * @return int
     */
    public function insert($data) {
        return Capsule::table('blog_comment')->insertGetId($data);
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Query\Builder
     */
    public function byEntryId($id) {
        return Capsule::table('blog_comment')->where('blog_entry_id', '=', $id)->take(5)->orderBy('updated_at','DESC');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function mostRecent() {
        return Capsule::table('blog_comment')->take(5)->orderBy('updated_at', 'DESC');
    }
}
