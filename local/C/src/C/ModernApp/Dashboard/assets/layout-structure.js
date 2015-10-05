$(document).ready(function layoutStructure () {

  $('.dashboard-content').on('click.layout', function (ev) {
    var target = $(ev.target);

    if (target.is('.layout-block-path')) {
      var next = target.toggleClass('open').parent().next();
      next.toggleClass('open');
      if (next.hasClass('open')) {
        var h = next.find('.layout-block-meta-content').outerHeight(true);
        next.css('height', h + "px")
      } else {
        next.css('height', '')
      }
      ev.stopImmediatePropagation();
      ev.stopPropagation();
    } else if (target.parent().is('.enable-help')) {
      ev.stopImmediatePropagation();
      ev.stopPropagation();
      var btn = target.parent();
      btn.toggleClass('enabled');
      var targetId = btn.attr('target')
      $('#'+targetId+"").toggleClass('preview-enabled')
    }

  })
});
