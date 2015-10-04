<?php

namespace C\Blog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class CommentForm extends AbstractType
{

//    public static function loadValidatorMetadata(ClassMetadata $metadata)
//    {
//        $metadata->addPropertyConstraint('email', new Assert\NotBlank());
//        $metadata->addPropertyConstraint('email', new Assert\Email());
//        $metadata->addPropertyConstraint('name', new Assert\NotBlank());
//        $metadata->addPropertyConstraint('comment', new Assert\NotBlank());
//    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'text', [
            'required'=>true,
            'constraints' => new Assert\Email()
        ]);
        $builder->add('name', 'text', ['required'=>!true]);
        $builder->add('comment', 'text', ['required'=>!true]);
    }

    public function getName()
    {
        return 'blog_comment';
    }
}
