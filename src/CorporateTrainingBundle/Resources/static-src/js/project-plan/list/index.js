import CheckTreeviewInput from '../../common/CheckTreeview-input';
import notify  from 'common/notify';

jQuery.validator.addMethod('endDate_check', function () {
  var startDate = $('[name="startDateTime"]').val();
  var endDate = $('[name="endDateTime"]').val();
  return (startDate <= endDate);
}, Translator.trans('project_plan.item.end_date_check_message'));

class Page {
  constructor() {
    this.init();
  }

  init() {
    this.initTreeViewInput();
    this.initSelect();
    this.initDatetimePicker();
    this.initvalidator();
    this.initOperate();
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

  initOperate() {

    $('.data-list').on('click', '.projectplan-operate', function () {
      let notifyTitle = $(this).data('notifyTitle');
      if (!confirm(Translator.trans('project_plan.secondary_confirmation')+`${notifyTitle}`)) {
        return;
      }

      $.get($(this).data('url'), function(result){
        if (result) {
          notify('success', `${notifyTitle}${Translator.trans('project_plan.success')}!`);
          window.location.reload();
        } else {
          notify('danger', `${notifyTitle}${Translator.trans('project_plan.error')}!`);
        }
      }).error(function() {
        notify('danger', `${notifyTitle}${Translator.trans('project_plan.error')}!`);
      });
    });
  }
}

new Page();
