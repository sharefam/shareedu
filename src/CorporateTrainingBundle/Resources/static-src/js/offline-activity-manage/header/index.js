import notify from 'common/notify';

export const publishOfflineActivity = () => {
  $('.offline-activity-publish-btn').click((event) => {
    if (!confirm(Translator.trans('offline_activity.publish.make_sure'))) {
      return;
    }
    $.post($(event.target).data('url'), function(data) {
      if (data.success) {
        notify('success', Translator.trans('offline_activity.publish.success_hint'));
        location.reload();
      } else {
        notify('danger', Translator.trans('offline_activity.publish.fail_hint') + ':' + data.message,5000);
      }
    });
  });
};

publishOfflineActivity();