$(document).ready(function dashboard () {
  var dashbaord = $('.dashboard')

  var openDashboard = function(ev){
    if (!dashbaord.hasClass('open')) {
      ev.stopPropagation();
      dashbaord.addClass('open')
      setTimeout(function(){
        $('.dashboard-content').off('mouseleave.dashboard').one('mouseleave.dashboard', closeDashboard)
      },600);
    }
  };
  var closeDashboard = function(ev){
    if (dashbaord.hasClass('open')) {
      ev.stopPropagation();
      dashbaord.removeClass('open')
      setTimeout(function(){
        $('.dashboard-handle').off('mouseenter.dashboard').one('mouseenter.dashboard', openDashboard)
      },600);
    }
  };
  $('.dashboard-handle').one('mouseenter.dashboard', openDashboard)

  $('.dashboard-title').on('click.dashboard', function (ev) {
    ev.stopImmediatePropagation();
    ev.stopPropagation();
    var target = $(ev.target);
    var next = target.next();


    if (!next.hasClass('open')) {

      var current = $('.dashboard-block-content.open').not(next);
      current.css('height', current.height()+'px');
      current.css('display', current.css('display'));
      current.css('height', '0px');
      setTimeout(function(){
        current.removeClass('open')
      }, 500)

      next.addClass('open');
      var h = 0;
      next.children().each(function (k, v) {
        h += $(v).outerHeight(true)
      });
       if(h===0) {
        next.css('height', "40px")
      } else {
        next.css('height', h + "px")
         setTimeout(function(){
           next.css('height', "auto")
         }, 500)
      }
    } else {
      next.css('height', next.height()+'px');
      next.css('display', next.css('display'));
      next.css('height', '0px');
      setTimeout(function(){
        next.removeClass('open')
      }, 500)
    }
  })
});
