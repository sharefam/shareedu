import notify from 'common/notify';

const $form = $('#offline-course-homework-check-form');
const $modal = $form.parents('.modal');

$('.js-save-btn').click(function () {
  $('.js-save-btn').button('loading');
  $.post($form.attr('action'), $form.serialize(),function (result) {
    if (result) {
      $modal.modal('hide');
      notify('success', Translator.trans('project_plan.exam-manage.review_success'));
      window.location.reload();
    } else {
      $('.js-save-btn').button('reset');
      notify('danger', Translator.trans('project_plan.save_error'));
    }
  }).error(function() {
    $('.js-save-btn').button('reset');
    notify('danger', Translator.trans('project_plan.save_error'));
  });
});
