define(function (require, exports) {
  let Notify = require('common/bootstrap-notify');
  let Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    let $form = $('#level-form');
    let $modal = $form.parents('.modal');

    let validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return;
        }

        $('#level-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize()).done(function (html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.teacher.submit_level_success'));
          window.location.reload();
        }).fail(function () {
          Notify.danger(Translator.trans('admin.teacher.submit_level_error'));
        });

      }
    });

    validator.addItem({
      element: '#name',
      required: true,
      rule: 'chinese_alphanumeric byte_maxlength{max:30} remote'
    });

  };
});
