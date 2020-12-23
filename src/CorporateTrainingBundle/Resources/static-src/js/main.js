//暂时先这样处理
import './common/jquery-blurr';


//isMobile
window.isMobile = function() {
  var userAgent = window.navigator.userAgent.toLocaleLowerCase();
  var exp = /iphone|android|symbianos|windows\sphone/g;
  return exp.test(userAgent);
}();

if (window.isMobile) {
  $('.study-center-user-bg').blurr({height: 187});
} else {
  $('.study-center-user-bg').blurr({height: 164});
}

$('.js-search-icon').mouseenter(function() {
  $(this).addClass('search-icon-lighter');
  $(this).next('.js-search').prop('placeholder', Translator.trans('widget.main.placeholder')).addClass('active');
  $(this).parents('.navbar-form').addClass('search-bar-space');
});

let flag = false;

$('.js-search').mouseleave(function() {
  if (!flag) {
    $(this).prop('placeholder', Translator.trans('widget.main.placeholder')).removeClass('active');
    $(this).parents('.navbar-form').removeClass('search-bar-space');
    $(this).prev('.js-search-icon').removeClass('search-icon-lighter');
  }
}).focus(function () {
  flag = true;
  $(this).prop('placeholder', '').addClass('active');
}).blur(function () {
  flag = false;
  $(this).prop('placeholder', Translator.trans('widget.main.placeholder')).removeClass('active');
  $(this).parents('.navbar-form').removeClass('search-bar-space');
  $(this).prev('.js-search-icon').removeClass('search-icon-lighter');
});

$(document).on('click', function(e) {
  $('.js-treeview-select-menu').removeClass('is-active');
});


$('[data-toggle="popover"]').popover();

$(document).on('click', '[data-toggle="scene-modal"]', function() {
  let self = $('#modal').get(0).__relation__; 
  self.remote($(this).data('url'));
});

$(document).on('click', '.js-back-icon', function() {
  window.history && window.history.back();
});
