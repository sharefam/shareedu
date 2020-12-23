import CheckTreeviewInput from '../../common/CheckTreeview-input';

class Page {
  constructor() {
    this.init();
  }

  init() {
    this.initvalidator();
    this.initTreeViewInput();
    this.initEvent();
  }

  initEvent() {
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

  initvalidator() {
    const $form = $('#member-statistic-data-list-form');

    const validator = $form.validate({
      rules: {
        progressStart: {
          max:100,
          min:0,
        },
        progressEnd: {
          max:100,
          min:0,
        },
      },
    });

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
      allowClear: true,
      width: 'off',
      placeholder: Translator.trans('project_plan.item.all_post')
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
