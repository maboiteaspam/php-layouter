<?php

namespace C\Blog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CommentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'text');
        $builder->add('name', 'text');
        $builder->add('comment', 'text');
    }

    public function getName()
    {
        return 'blog_comment';
    }
}
