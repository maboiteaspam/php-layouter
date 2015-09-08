<script type="text/javascript">
    $.get('<?php echo $url; ?>', function(data){
        $('#<?php echo $id; ?>').replaceWith(data);
        $(document).trigger('c_block_loaded', '#<?php echo $target; ?>')
    });
</script>
