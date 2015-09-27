<?php
/* @var $this \C\View\ConcreteContext */
/* @var $form \Symfony\Component\Form\FormView */
?>

<?php
echo $this->form_start($form);
echo $this->form_row($form['email'], ['label'=>'Email', 'type'=>'email']);
echo $this->form_row($form['gender'], ['label'=>'Gender', 'type'=>'choice']);
echo $this->submit_widget($form['post'], ['label'=>'Post']);
echo $this->form_end($form);
?>
<br/>
<br/>
ok