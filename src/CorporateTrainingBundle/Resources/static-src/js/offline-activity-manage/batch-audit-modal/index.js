import notify  from 'common/notify';
import Cookies from 'js-cookie';

$('#batch-record-num').text($('#selected-count').text());

$('#batch-pass-btn').click(function () {
  $(this).button('submiting').addClass('disabled');

  let recordIds = $('#recordIds').val();
  $.post($(this).data('url'), {recordIds:recordIds}, function(result){
    if (result) {
      notify('success', Translator.trans('offline_activity.batch_audit.success_hint'));
      window.location.reload();
    } else {
      notify('danger', Translator.trans('offline_activity.batch_audit.fail_hint'));
    }
  }).success(function(){
    recordIds = Cookies.get('recordIds').split(',');
    recordIds.splice(0, recordIds.length);
    Cookies.set('recordIds', recordIds.join(','));
  }).error(function() {
    notify('danger', Translator.trans('offline_activity.batch_audit.fail_hint'));
  });
});

$('#batch-reject-btn').click(function () {
  $(this).button('submiting').addClass('disabled');

  let recordIds = $('#recordIds').val();
  $.post($(this).data('url'), {recordIds:recordIds}, function(result){
    if (result) {
      notify('success', Translator.trans('offline_activity.batch_audit.success_hint'));
      window.location.reload();
    } else {
      notify('danger', Translator.trans('offline_activity.batch_audit.fail_hint'));
    }
  }).success(function(){
    recordIds = Cookies.get('recordIds').split(',');
    recordIds.splice(0, recordIds.length);
    Cookies.set('recordIds', recordIds.join(','));
  }).error(function() {
    notify('danger', Translator.trans('offline_activity.batch_audit.fail_hint'));
  });
});
