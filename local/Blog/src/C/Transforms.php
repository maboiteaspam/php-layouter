<?php

namespace C\Blog;

use C\HTMLLayoutBuilder\Transforms as HTMLTransforms;

class Transforms extends HTMLTransforms{

    /**
     * @param mixed $app
     * @return Transforms
     */
    public static function transform ($app) {
        return new Transforms($app);
    }

    function home () {
        $this->setTemplate('body_content', __DIR__.'/templates/entry-list.php');
        $this->updateData('body_content', [
            'entries'=>[
                [
                    'id'=>0,
                    'date'=> date('Y-m-d H:i:s'),
                    'author'=>'some',
                    'img_alt'=>'some',
                    'title'=>'some',
                    'content'=>'blog entry',
                    'comments'=>[],
                ],
                [
                    'id'=>0,
                    'date'=> date('Y-m-d H:i:s'),
                    'author'=>'some',
                    'img_alt'=>'some',
                    'title'=>'some',
                    'content'=>'blog entry',
                    'comments'=>[],
                ],
            ],
        ]);
        $this->setTemplate('body_footer', __DIR__.'/templates/footer.php');
        $this->updateData('body_footer', [
            'year'=> date('Y'),
        ]);
        return $this;
    }
    function detail () {
        $this->setTemplate('body_content', __DIR__.'/templates/entry-detail.php');
        $this->updateData('body_content', [
            'entry'=>[
                'id'=>0,
                'date'=> date('Y-m-d H:i:s'),
                'author'=>'some',
                'img_alt'=>'some',
                'title'=>'some',
                'content'=>'blog entry',
                'comments'=>[],
            ],
        ]);
        $this->setTemplate('blog_detail_comments', __DIR__.'/templates/entry-comments.php');
        $this->updateData('blog_detail_comments', [
            'comments'=> [],
        ]);
        $this->setTemplate('blog_form_comments', __DIR__.'/templates/form-comment.php');
        $this->updateData('blog_form_comments', [
            'form'=> null,
        ]);
        $this->setTemplate('body_footer', __DIR__.'/templates/footer.php');
        $this->updateData('body_footer', [
            'year'=> date('Y'),
        ]);
        return $this;
    }
}
