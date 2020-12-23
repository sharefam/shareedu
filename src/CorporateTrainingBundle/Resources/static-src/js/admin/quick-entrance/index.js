let currentItem = [];
const $quickNavigation = $('.quick-navigation');

$(document).on('click', '.model-item .item-container', function(){
  $(this).toggleClass('active');
});
$(document).on('click','#role-submit', function(){
  const $quickItem = $(this).parent().parent().parent().find('.item-container');
  $quickItem.each((index, item) => {
    let code = $(item).data('code');
    let flag = $(item).hasClass('active');
    if(flag && !currentItem.includes(code)) {
      currentItem.push(code);
    }
  });

  let $form = $('#quick-entrance-form');
  $.post($form.attr('action'),{'_csrf_token': $('meta[name=csrf-token]').attr('content'),data: JSON.stringify(currentItem)}, function(data){
    if(data) {
      $quickNavigation.html(data);
      $('#modal').modal('hide');
      currentItem = [];
    }
  });
});