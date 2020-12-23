import notify from 'common/notify';

let total = $('#offline-activity-container').data('total');
let currentIndex = 0;
let numPerPage = 5;

if (total > 0) {
  ajaxGetRowHtml($('#offline-activity-container').data('url'));
  $(window).scroll(function(){
    if($(window).scrollTop() + $(window).height() == $(document).height()) {
      $('.js-offline-activity-more').trigger('click');
    }
  });
} else {
  $('#offline-activity-container').append(Translator.trans('offline_activity.list.empty'));
  $('.js-offline-activity-more').addClass('hidden');
}

function ajaxGetRowHtml(url){
  $('.js-offline-activity-more').html(Translator.trans('offline_activity.list.loading'));
  $('.js-offline-activity-more').addClass('disabled');

  url = url+'&start='+currentIndex;
  $.ajax({
    url: url,
    type: 'get',
    dataType: 'html',
  }).success( function(data) {
    $('#offline-activity-container').append(data);
    currentIndex = currentIndex+numPerPage;
    if(currentIndex >= total){
      $('.js-offline-activity-more').addClass('hidden');
    }
  }).fail( function(err) {
    notify('danger',Translator.trans(Translator.trans('offline_activity.list.loading_fail')));
  }).complete( function(){
    $('.js-offline-activity-more').html(Translator.trans('offline_activity.list.loading_more'));
    $('.js-offline-activity-more').removeClass('disabled');
  });
}

$('.js-offline-activity-more').on('click', function(e) {
  if($(this).hasClass('disabled')){
    return;
  }
  let url = $('#offline-activity-container').data('url');
  ajaxGetRowHtml(url);
});