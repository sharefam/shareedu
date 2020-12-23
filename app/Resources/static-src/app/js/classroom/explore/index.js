echo.init();

$('#free').on('click', function(event) {
  window.location.href = $(this).val();
});

$('.js-filter-action').on('click', function(){
  const $icon = $(this).children('.es-icon');
  
  if($icon.hasClass('es-icon-keyboardarrowup') ) {
    $('.js-tab-filter').removeClass('no-bor');
    $icon.removeClass('es-icon-keyboardarrowup').addClass('es-icon-keyboardarrowdown');

  } else {
    $('.js-tab-filter').addClass('no-bor');
    $icon.removeClass('es-icon-keyboardarrowdown').addClass('es-icon-keyboardarrowup');
  }
 
  $('.js-tabs-container').toggleClass('hidden');
});