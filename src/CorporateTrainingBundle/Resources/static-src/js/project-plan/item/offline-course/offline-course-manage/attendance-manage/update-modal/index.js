import notify from 'common/notify';

const $form = $('#attendance-update-form');
const $modal = $form.parents('.modal');

$('.js-update-attendance-btn').click(function () {
  $('.js-update-attendance-btn').button('loading');
  $.post($form.data('url'), $form.serialize(),function (result) {
    if (result) {
      $modal.modal('hide');
      notify('success', Translator.trans('project_plan.save_success'));
      window.location.reload();
    } else {
      $('.js-update-attendance-btn').button('reset');
      notify('danger', Translator.trans('project_plan.save_error'));
    }
  }).error(function() {
    $('.js-update-attendance-btn').button('reset');
    notify('danger', Translator.trans('project_plan.save_error'));
  });
});

$('[data-toggle="popover"]').popover();
