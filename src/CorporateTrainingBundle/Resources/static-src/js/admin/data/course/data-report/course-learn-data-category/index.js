import OverviewDateRangePicker from '../../../../../common/date-range-picker';


class Page {
  constructor() {
    this.init();
  }

  init() {
    $('[data-toggle="popover"]').popover();
    new OverviewDateRangePicker('#date-range-picker');
    this.initTreeViewInput();
    this.initValidator();
    this.initAjaxPaginator();
    this.initPostExporter();
  }

  initAjaxPaginator() {
    $('.data-list').on('click', '.pagination li', function () {
      var url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('#course-learn-data-statistic-department').serialize(), function (data) {
          $('.data-list').html(data);
          $('[data-toggle="popover"]').popover();
        });
      }
    });
  }

  initPostExporter() {
    $('.js-exporter').on('click', function () {
      var exportForm = $('#course-learn-data-statistic-department');
      var url = $(this).data('url');
      $(exportForm).attr('action', url);
      exportForm.submit();
      $(exportForm).attr('action', '');
    });
  }

  initValidator() {
    const $form = $('#course-learn-data-statistic-department');

    var $postContainer = $('#postId');
    $postContainer.select2({
      ajax: {
        url: $postContainer.data('url'),
        dataType: 'json',
        quietMillis: 100,
        data: function (term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results: function (data) {
          var results = [];
          $.each(data, function (index, item) {

            results.push({
              id: item.id,
              name: item.name
            });
          });

          return {
            results: results
          };

        }
      },
      initSelection: function(element, callback) {
        var id = $(element).val();
        if (id !== '') {
          var name = $(element).data('name');
          callback({id:id, name:name});
        }
      },
      formatSelection: function (item) {
        return item.name;
      },
      formatResult: function (item) {
        return item.name;
      },
      formatSearching: function () {
        return Translator.trans('site.searching_hint');
      },
      formatNoMatches: function() {
        return Translator.trans('select.format_no_matches');
      },
      allowClear: true,
      width: 'off',
      placeholder: Translator.trans('my.department.data_report.all_post')
    });

    $('.js-data-popover').popover({
      html: true,
      trigger: 'hover',
      placement: 'top',
      template: '<div class="popover tata-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
      content: function() {
        return $(this).siblings('.popover-content').html();
      }
    });
  }

  initTreeViewInput() {
    new window.$.CheckTreeviewInput({
      $elem: $('#resource-orgCode'),
      saveColumn: 'id',
      disableNodeCheck: false,
      transportChildren: true,
    });
  }
}

new Page();
