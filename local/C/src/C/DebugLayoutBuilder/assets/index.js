(function debugLayout () {

  var setTooltipster = function(whatever, el){
    el = $(el);
    if (el.is('c_block_node[id]') && !el.attr('id').match(/root/) ){
      el.children().attr('title', el.attr('caller')).tooltipster({
        onlyOne: true,
        functionBefore: function (o,continueTooltip) {
          el.children().addClass('debug');
          continueTooltip();
        },
        functionAfter: function () {
          el.children().removeClass('debug');
        }
      })
    }
  }
  $("c_block_node").each(setTooltipster);
  $(document).on('c_block_loaded', setTooltipster)
})();
