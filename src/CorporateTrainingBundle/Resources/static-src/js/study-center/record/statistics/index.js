import AjaxPaginator from '../../../common/ajax-paginator';

$('#statisticsDetailBtn').on('click', (e) => {
  let $target = $(e.target);
  if ($('.ajax-paginator-content').html().trim() != '') {
    return;
  }
  $('.ajax-paginator-content').html(`<div class="text-center">${Translator.trans('study_center.newbie_guide.loading')}</div>`);

  $.get($target.data('url'), function (html) {
    $('.ajax-paginator-content').html(html);
    new AjaxPaginator();
  });
});

