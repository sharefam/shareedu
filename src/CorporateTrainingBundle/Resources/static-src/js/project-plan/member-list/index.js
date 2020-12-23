import notify  from 'common/notify';

class List {
  constructor() {
    this.initAssignmentOperate();
  }

  initAssignmentOperate() {
    $('.projectplan-operate').click(function () {

      let notifyTitle = $(this).data('notifyTitle');
      if (!confirm(`${Translator.trans('project_plan.secondary_confirmation')}${notifyTitle}`)) {
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

new List();



