$('.js-exporter').on('click', function () {
  let exportForm = $('.form-inline');
  let url = $(this).data('url');
  $(exportForm).attr('action', url);
  exportForm.submit();
  $(exportForm).attr('action', '');
});