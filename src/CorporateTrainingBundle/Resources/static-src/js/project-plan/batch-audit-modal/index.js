import notify  from 'common/notify';
import Cookies from 'js-cookie';

$('#batch-record-num').text($('#selected-count').text());

$('#batch-pass-btn').click(function () {
  $(this).button('submiting').addClass('disabled');

  let recordIds = $('#recordIds').val();
  $.post($(this).data('url'), {recordIds:recordIds}, function(result){
    if (result) {
      notify('success', Translator.trans('project_plan.operate_success'));
      window.location.reload();
    } else {
      notify('danger', Translator.trans('project_plan.operate_error'));
    }
  }).success(function(){
    recordIds = Cookies.get('recordIds').split(',');
    recordIds.splice(0, recordIds.length);
    Cookies.set('recordIds', recordIds.join(','));
  }).error(function() {
    notify('danger', Translator.trans('project_plan.operate_error'));
  });
});

$('#batch-reject-btn').click(function () {
  $(this).button('submiting').addClass('disabled');

  let recordIds = $('#recordIds').val();
  $.post($(this).data('url'), {recordIds:recordIds}, function(result){
    if (result) {
      notify('success', Translator.trans('project_plan.operate_success'));
      window.location.reload();
    } else {
      notify('danger', Translator.trans('project_plan.operate_error'));
    }
  }).success(function(){
    recordIds = Cookies.get('recordIds').split(',');
    recordIds.splice(0, recordIds.length);
    Cookies.set('recordIds', recordIds.join(','));
  }).error(function() {
    notify('danger', Translator.trans('project_plan.operate_error'));
  });
});
