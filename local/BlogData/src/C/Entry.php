<?php

namespace C\BlogData;

use Illuminate\Database\Capsule\Manager as Capsule;

class Entry{
    /**
     * @param array $data
     * @return int
     */
    public static function insert($data) {
        return Capsule::table('blog_entry')->insertGetId($data);
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Query\Builder
     */
    public static function byId($id) {
        return Capsule::table('blog_entry')->where('id', '=', $id)->take(1);
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public static function mostRecent() {
        return Capsule::table('blog_entry')->take(20)->orderBy('updated_at', 'DESC');
    }
}
