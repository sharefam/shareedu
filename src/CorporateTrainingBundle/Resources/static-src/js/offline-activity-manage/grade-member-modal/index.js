import notify  from 'common/notify';

let $form = $('#grade-member-form');
let $modal = $form.parents('.modal');
let $btn = $('#grade-member-submit');
let id = $form.data('id');

let validator = $form.validate({
  onkeyup: false,
  rules: {
    score: {
      required: true,
      number: true,
      max: 100,
      min: 0,
    },
    evaluate: {
      byte_maxlength: 280,
    }
  },
});

$btn.click(() => {
  $btn.button('submiting').addClass('disabled');
  let validate = validator.form();
  if (validate) {
    $.post($form.prop('action'), $form.serialize(), function(html){
      $('#member-list-table').children('tbody').children('tr#member-'+id).replaceWith(html);
    }).success(function(){
      notify('success', Translator.trans('offline_activity.exam.success_hint'));
      $modal.modal('hide');
    }).error(function() {
      notify('danger', Translator.trans('offline_activity.exam.fail_hint'));
      $btn.button('reset').removeClass('disabled');
    });
  }
});