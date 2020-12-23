import notify  from 'common/notify';

class Page {
  constructor() {
    this.init();
  }

  init() {
    this.initDatetimePicker();
    this.initOperate();
  }

  initDatetimePicker() {
    const defaultOpts = {
      language: document.documentElement.lang,
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 'month',
      startView: 2
    };
    $('[name=startTime]').datetimepicker(defaultOpts);

    $('[name=endTime]').datetimepicker(defaultOpts);

    $('[name=startTime]').on('changeDate',function(){
      $('[name=endTime]').datetimepicker('setStartDate',$('[name=startTime]').val().substring(0,16));
    });

    $('[name=endTime]').on('changeDate',function(){
      $('[name=startTime]').datetimepicker('setEndDate',$('[name=endTime]').val().substring(0,16));
    });
  }

  initOperate() {
    $('.exam-operate').click((e) => {

      let notifyTitle = $(e.target).data('notifyTitle');
      if (!confirm(Translator.trans('project_plan.secondary_confirmation')+`${notifyTitle}`)) {
        return;
      }

      $.post($(e.target).data('url'), function(result){
        if (result.status == 'error') {
          notify('danger', result.message);
        } else if (result) {
          notify('success', `${notifyTitle}${Translator.trans('project_plan.success')}!`);
          window.location.reload();
        } else {
          notify('danger', `${notifyTitle}${Translator.trans('project_plan.error')}!`);
        }
      }).error(function() {
        notify('danger', `${notifyTitle}${Translator.trans('project_plan.error')}!`);
      });
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
}

new Page();
