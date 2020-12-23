$('body').on('mouseenter', '.study-center-manage-dropdown', function (event) {
  $(this).addClass('open');
}).on('mouseleave', '.study-center-manage-dropdown', function (event) {
  $(this).removeClass('open');
});

$('.set_mask').css({'zIndex':'101'});