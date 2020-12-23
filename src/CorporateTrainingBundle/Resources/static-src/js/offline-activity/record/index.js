$('.botton-menus').on('click', 'a', function() {
  $.post($(this).data('url'), function(result){
    
    $('.botton-menus').html(result);
  });
});