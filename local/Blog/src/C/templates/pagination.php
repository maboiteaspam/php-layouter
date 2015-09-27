<?php
/* @var $this \C\View\ConcreteContext */
/* @var $count int */
/* @var $by int */
/* @var $labelFormat string */
/* @var $routeName string */
/* @var $routeParams [] */

$routeParams = isset($routeParams)?$routeParams:[];
?>

<div class="pagination">
    <?php for($i=0;$i<$count/$by;$i++) { ?>
        <a href='<?php echo $this->urlFor($routeName, array_merge($routeParams, ['page'=>$i])); ?>'>
            <?php echo $this->format($labelFormat, array_merge($routeParams, ['page'=>$i])); ?>
        </a>
    <?php } ?>
</div>


