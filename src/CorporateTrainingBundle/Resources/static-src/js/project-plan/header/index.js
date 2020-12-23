import notify from 'common/notify';

export const publishOfflineCourse = () => {

  $('.projectplan-publish-btn').click((event) => {
    let notifyTitle = $('.projectplan-publish-btn').data('notifyTitle');
    if (!confirm(Translator.trans('project_plan.secondary_confirmation')+`${notifyTitle}？`)) {
      return;
    }
    $.post($(event.target).data('url'), function(data) {
      if (data.success) {
        notify('success', `${notifyTitle}`+Translator.trans('project_plan.success'));
        location.reload();
      } else {
        notify('danger',`${notifyTitle}`+Translator.trans('project_plan.error')+'：' + data.message, 5000);
      }
    });
  });
};

publishOfflineCourse();
