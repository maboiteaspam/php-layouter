<?php
/* @var $this \C\View\ConcreteContext */
/* @var $url string */
/* @var $target string */
/* @var $id string */
?>
<script type="text/javascript">
    $.get('<?php echo $url; ?>?target=<?php echo $target; ?>', function(data){
        var receiver = $('#<?php echo $id; ?>');
        if ($(data).length) {
            data = $(data);
            if (data.first().is("c_block_node")) {
                if (data.children().length) {
                    data = data.children().unwrap();
                } else {
                    data = data.html();
                }
            }
        }
        receiver.replaceWith(data);
        $(document).trigger('c_block_loaded', '#<?php echo $target; ?>')
    });
</script>
