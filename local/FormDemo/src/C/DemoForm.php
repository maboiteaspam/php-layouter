<?php

namespace C\FormDemo;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DemoForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('gender', 'choice', ['choices'=>['men','women',]]);
        $builder->add('email', 'email');
        $builder->add('post', 'submit');
    }

    public function getName()
    {
        return 'demo_form';
    }
}
