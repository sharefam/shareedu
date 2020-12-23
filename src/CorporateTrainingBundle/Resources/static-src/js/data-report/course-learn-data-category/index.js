import CheckTreeviewInput from '../../common/CheckTreeview-input';

jQuery.validator.addMethod('endDate_check', function () {
  var startDate = $('[name="startDateTime"]').val();
  var endDate = $('[name="endDateTime"]').val();
  return (startDate <= endDate);
}, Translator.trans('my.department.data_report.endDate_check'));

class Page {
  constructor() {
    this.init();
  }

  init() {
    $('[data-toggle="popover"]').popover();

    this.initTreeViewInput();
    this.initSelect();
    this.initDatetimePicker();
    this.initvalidator();
    this.initAjaxPaginator();
    this.initPostExporter();
  }

  initSelect() {
    $('[data-role="tree-select"], [name="categoryId"]').select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first',
      allowClear: true,
    });
  }

  initAjaxPaginator() {
    $('.data-list').on('click', '.pagination li', function () {
      var url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('.department-manage-search-form').serialize(), function (data) {
          $('.data-list').html(data);
          $('[data-toggle="popover"]').popover();
        });
      }
    });
  }

  initPostExporter() {
    $('.js-exporter').on('click', function () {
      var exportForm = $('.form-inline');
      var url = $(this).data('url');
      $(exportForm).attr('action', url);
      exportForm.submit();
      $(exportForm).attr('action', '');
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

    $('[name=startDateTime]').on('changeDate',function(){
      $('[name=endDateTime]').datetimepicker('setStartDate',$('[name=startDateTime]').val().substring(0,16));
    });

    $('[name=endDateTime]').on('changeDate',function(){
      $('[name=startDateTime]').datetimepicker('setEndDate',$('[name=endDateTime]').val().substring(0,16));
    });
  }

  initvalidator() {
    const $form = $('.department-search-form');
    // const validator = $form.validate({
    //     rules: {
    //       endDateTime: {
    //         endDate_check:true,
    //         required: {
    //           depends () {
    //             $(this).val($.trim($(this).val()));
    //             return true;
    //           },
    //         },
    //       },
    //       startDateTime: {
    //         required: {
    //           depends () {
    //             $(this).val($.trim($(this).val()));
    //             return true;
    //           },
    //         },
    //       },
    //     },
    //     messages: {
    //       startDateTime: {
    //           required: Translator.trans('请输入开始时间'),
    //           trim: Translator.trans('请输入开始时间')
    //       },
    //       endDateTime: {
    //           required: Translator.trans('请输入结束时间'),
    //           trim: Translator.trans('请输入结束时间')
    //       }
    //     },
    //     submitHandler(form) {
    //       let $form = $(form);
    //       let settings = this.settings;
    //       let $btn = $(settings.currentDom);
    //       if (!$btn.length) {
    //         $btn = $(form).find('[type="submit"]');
    //       }
    //
    //       // $btn.button('loading');
    //       // $('.js-table-wrap').empty().append('正在加载中');
    //
    //       // $.post($form.attr('action'), $form.serializeArray(), (data) => {
    //       //   // $('.js-table-wrap').empty().append('hehehee');
    //       //   // $btn.button('reset');
    //       // }).error((data) => {
    //       //   $('.js-table-wrap').empty().append('加载失败...');
    //       //   $btn.button('reset');
    //       // });
    //     }
    // });

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
