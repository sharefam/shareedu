import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
import notify from 'common/notify';

let $form = $('#submit-homework-form');
let $btn = $('#homework-btn');

$btn.click((event) => {
  $.post($form.attr('action'), $form.serialize(), function (data) {
    if(data['success']) {
      $btn.button('loading');
      notify('success', data['message']);
      window.location.reload();
    } else {
      notify('danger', data['message']);
    }
  }).error(function () {
    notify('danger', Translator.trans('study_center.record.submit_homework'));
  });
});

new AttachmentActions($form);
$('[data-toggle="tooltip"]').tooltip();