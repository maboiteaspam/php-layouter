(function debugLayout () {

  $("c_block_node").each(function(k, v){

    if (!$(v).attr('id').match(/root/) ){
      $(v).off('mouseover mouseout');
      $(v).on('mouseover', function(ev){
        ev.stopImmediatePropagation();
        $(this).children().addClass('debug')
      });
      $(v).on('mouseout', function(ev){
        ev.stopImmediatePropagation();
        $(this).children().removeClass('debug')
      });
      $(v).on('mouseout', function(){
      });
    }
  });

})();
