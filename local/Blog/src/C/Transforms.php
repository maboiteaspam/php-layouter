<?php

namespace C\Blog;

use C\LayoutBuilder\Transforms as BaseTransforms;

class Transforms extends BaseTransforms{

    function home () {
        $this->setTemplate('body_content', __DIR__.'/templates/entry-list.php');
        $this->updateData('body_content', [
            'entries'=>[
                (object) [
                    'id'=>0,
                    'date'=> date('Y-m-d H:i:s'),
                    'author'=>'some',
                    'img_alt'=>'some',
                    'title'=>'some',
                    'content'=>'blog entry',
                    'comments'=>[],
                ],
                (object) [
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
            'entry'=> (object) [
                'id'=>0,
                'date'=> date('Y-m-d H:i:s'),
                'author'=>'some',
                'img_alt'=>'some',
                'title'=>'some',
                'content'=>'blog entry',
                'comments'=>[],
            ],
        ]);
        $this->updateData('body_content', [
            'entry'=> (object) [
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
