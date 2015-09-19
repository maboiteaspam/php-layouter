<?php

namespace C\BlogData;

use Illuminate\Database\Capsule\Manager as Capsule;
use \C\Schema\ISchema;
use \C\BlogData\Eloquent\Entry as Entry;
use \C\BlogData\Eloquent\Comment as Comment;

class Schema implements ISchema{
    public function createTables(Capsule $capsule) {
        $builder = $capsule->getConnection()->getSchemaBuilder();
        $builder->create('blog_entry', function($table) {
            $table->increments('id');
            $table->string('title');
            $table->string('author');
            $table->string('img_alt');
            $table->string('content');
            $table->enum('status', array('VISIBLE', 'HIDDEN'));
            $table->timestamps();
        });
        $builder->create('blog_comment', function($table) {
            $table->increments('id');
            $table->string('author');
            $table->string('content');
            $table->enum('status', array('VISIBLE', 'HIDDEN'));
            $table->timestamps();
            $table->integer('blog_entry_id');
        });
    }
    public function dropTables(Capsule $capsule) {
        $builder = $capsule->getConnection()->getSchemaBuilder();
        $builder->drop('blog_entry');
        $builder->drop('blog_comment');
    }
    public function populateTables(Capsule $capsule) {
        $capsule->getConnection()->transaction(function(){
            $entryModel = new Entry();
            $commentModel = new Comment();
            $fixtureEntries = include(__DIR__ . '/fixtures/blog-entries.php');
            foreach ($fixtureEntries as $entry) {
                $comments = $entry['comments'];
                unset($entry['comments']);
                unset($entry['id']);
                $id = $entryModel->insert($entry);
                foreach ($comments as $comment) {
                    unset($comment['id']);
                    $comment['blog_entry_id'] = $id;
                    $commentModel->insert($comment);
                }
            }
        });
    }
}
