
class Page
{
  constructor() {
    this.init();
  }

  init() {
    (function initTreeviewInput() {
      new window.$.CheckTreeviewInput({
        $elem: $('#resource-orgCode'),
        saveColumn: 'id',
        disableNodeCheck: false,
        transportChildren: true,
      });
    })();

    $('.data-list').on('click', '.pagination li', function () {
      let url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('.department-manage-search-form').serialize(), function (data) {
          $('.data-list').html(data);
          $('[data-toggle="popover"]').popover();
        });
      }
    });

    $('.js-exporter').on('click', function () {
      let exportForm = $('.form-inline');
      let url = $(this).data('url');
      $(exportForm).attr('action', url);
      exportForm.submit();
      $(exportForm).attr('action', '');
    });
  }
}

new Page();