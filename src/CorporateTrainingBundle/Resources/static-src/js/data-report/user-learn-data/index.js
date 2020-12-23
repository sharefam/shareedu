import CheckTreeviewInput from '../../common/CheckTreeview-input';

jQuery.validator.addMethod('endDate_check', function() {
  var startDate = $('[name="startDateTime"]').val();
  var endDate = $('[name="endDateTime"]').val();
  return (startDate <= endDate);
}, Translator.trans('my.department.data_report.endDate_check'));

class Page {
  constructor() {
    this.init();
  }

  init() {
    this.initSelect();
    this.initDatetimePicker();
    this.initvalidator();
    this.initTreeviewInput();
    this.initEvent();
  }

  initEvent() {
    $('.data-list').on('click', '.pagination li', function () {
      var url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('.department-manage-search-form').serialize(), function (data) {
          $('.data-list').html(data);
          $('[data-toggle="popover"]').popover();
        });
      }
    });

    $('.js-exporter').on('click', function () {
      var exportForm = $('.form-inline');
      var url = $(this).data('url');
      $(exportForm).attr('action', url);
      exportForm.submit();
      $(exportForm).attr('action', '');
    });
  }

  initSelect() {
    $('[data-role="tree-select"], [name="categoryId"]').select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first'
    });
  }

  initDatetimePicker() {
    const defaultOpts = {
      language: 'zh',
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 'month'
    };
    $('[name=startDateTime]').datetimepicker(defaultOpts);

    $('[name=endDateTime]').datetimepicker(defaultOpts);
  }

  initvalidator() {
    let $postContainer = $('#postId');
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
  }

  initTreeviewInput() {
    let $treeview = $('.js-treeview-select-wrap');
    for (let i = 0; i < $treeview.length; i++) {
      new CheckTreeviewInput({ $elem: $treeview.eq(i) });
    }
  }
}

new Page();
