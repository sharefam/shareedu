import notify  from 'common/notify';

$('#member-list-table').on('click', '.member-remove', function () {
  if (!confirm(Translator.trans('offline_activity.delete.make_sure'))) {
    return;
  }

  let id = $(this).data('id');
  $.post($(this).data('url'), function(result){
    if (result) {
      $('#member-list-table').children('tbody').children('tr#member-'+id).remove();
      notify('success', Translator.trans('offline_activity.delete.success_hint'));
    } else {
      notify('success', Translator.trans('offline_activity.delete.fail_hint'));
    }
  }).error(function() {
    notify('success', Translator.trans('offline_activity.delete.fail_hint'));
  });
});
