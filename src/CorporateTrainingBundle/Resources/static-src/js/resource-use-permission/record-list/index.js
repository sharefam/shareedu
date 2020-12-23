import 'common/tabs-lavalamp/index';
import notify from 'common/notify';

import tabBlock from '../../common/tab-block';
new tabBlock({ element: '.js-record-list', mode: 'click', cb: function(e) {
  const $elem = $(this.element);
  const $currentTarget = $(e.currentTarget);
  const index = ($currentTarget).index();
  if (index == 0) {
    $('#history-list-form').addClass('hidden');
  } else {
    $('#history-list-form').removeClass('hidden');
  }
}});

$('.data-list').on('click', '.pagination li', function () {
  let  url = $(this).data('url');
  let self = $(this);
  if (typeof (url) !== 'undefined') {
    $.post(url,function (data) {
      self.parents('.data-list').html(data);
      $('[data-toggle="popover"]').popover();
    });
  }
});

$('#history-list-form').on('click', '.js-submit', function () {
  let  url = $('#history-list-form').data('url');
  if (typeof (url) !== 'undefined') {
    $.post(url,$('#history-list-form').serialize(),function (data) {
      $('.js-history-data-list').html(data);
      $('[data-toggle="popover"]').popover();
    });
  }
});

$('.js-tab-link').on('click', function () {
  let  url = $(this).data('url');
  let self = $(this);
  if (typeof (url) !== 'undefined') {
    $.post(url,function (data) {
      $(`.${self.data('type')}`).html(data);
      $('[data-toggle="popover"]').popover();
    });
  }
});

$('.use-permission-body', window.parent.document).keydown(function (event) {
  if (event.keyCode == 13) {
    return false;
  }
});

$('.data-list').on('click', '.js-canceled-shared', function () {
  let  url = $(this).data('url');
  let  sharedUrl = $(this).data('sharedUrl');
  let self = $(this);
  if (typeof (url) !== 'undefined') {
    $.post(url,function (data) {
        
    }).done((data) => {

      if(data.success){
        $('.js-record-count',window.parent.document).html(data.sharedCount);
        $.post(sharedUrl,function (data) {
          self.parents('.data-list').html(data);
          notify('success',Translator.trans('resource.canceled_shared.message'));
          $('[data-toggle="popover"]').popover();
        });
      }

    });
  }
});