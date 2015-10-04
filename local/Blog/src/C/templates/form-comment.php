<?php
/* @var $this \C\View\ConcreteContext */
/* @var $form Symfony\Component\Form\FormView  */
?>

FORM FORM FORM

<?php
echo date("Y-m-d H:i:s");
echo $this->form_start($form);
echo $this->form_rows($form);
echo $this->submit_widget($form, ['label'=>'submit']);
echo $this->form_end($form);
?>
