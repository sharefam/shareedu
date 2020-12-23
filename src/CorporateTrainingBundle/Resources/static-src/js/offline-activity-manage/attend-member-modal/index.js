import notify  from 'common/notify';

let $form = $('#attend-member-form');
let $modal = $form.parents('.modal');
let $btn = $('#attend-member-submit');
let id = $form.data('id');

$btn.click(() => {
  $btn.button('submiting').addClass('disabled');
  $.post($form.prop('action'), $form.serialize(), function(html){
    $('#member-list-table').children('tbody').children('tr#member-'+id).replaceWith(html);
  }).success(function(){
    notify('success', Translator.trans('offline_activity.attend_success_hint'));
    $modal.modal('hide');
  }).error(function() {
    notify('danger', Translator.trans('offline_activity.attend_fail_hint'));
    $btn.button('reset').removeClass('disabled');
  });
});