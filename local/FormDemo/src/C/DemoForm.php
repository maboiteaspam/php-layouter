<?php

namespace C\FormDemo;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DemoForm extends AbstractType
{

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
//        $metadata->addPropertyConstraint('gender', new NotBlank());
        $metadata->addPropertyConstraint('email', new NotBlank());
        $metadata->addPropertyConstraint('email', new Email());
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('gender', 'choice', [
            'choices'           =>['men','women',],
            'label'             => 'Gender',
        ]);

        $builder->add('email', 'email', [
            'label'     => 'Your email',
            'required'  => false,
//            'type'=>'text',
            'attr' => ["maxlength" => "4", "size" => "4"],
        ]);
        $builder->add('post', 'submit');
        $builder->add('save', 'submit', array('label' => 'Create Task'));
    }

    public function getName()
    {
        return 'demo_form';
    }
}
