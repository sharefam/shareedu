import notify  from 'common/notify';
import BatchSelectInCookie from 'app/common/widget/batch-select-in-cookie';

let batchSelectInCookie = new BatchSelectInCookie($('#project-plan-member-container'), 'projectPlanMemberIds');

class MemberDelete {
  constructor() {
    this.init();
  }
  
  init() {
    $('#member-delete').click(function () {
      
      let notifyTitle = $(this).data('notifyTitle');
      
      $.post($(this).data('url'), function(result){
        if (result) {
          notify('success', `${notifyTitle}${Translator.trans('project_plan.success')}!`);
          batchSelectInCookie._clearCookie();
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

new MemberDelete();



