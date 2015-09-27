<?php
/* @var $this \C\View\ConcreteContext */
/* @var $url string */
/* @var $target string */
/* @var $id string */
?>
<script type="text/javascript">
    $.get('<?php echo $url; ?>?target=<?php echo $target; ?>', function(data){
        data = $(data);
        if ($(data).first().is("c_block_node")) {
            $(data).unwrap();
        }
        $('#<?php echo $id; ?>').replaceWith(data);
        $(document).trigger('c_block_loaded', '#<?php echo $target; ?>')
    });
</script>
