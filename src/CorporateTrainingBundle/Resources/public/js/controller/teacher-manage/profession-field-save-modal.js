define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    var $form = $('#teacher-manage-form');
    var $modal = $form.parents('.modal');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return;
        }
        $('#profession-create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function (html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.teacher.field.save_success'));
          window.location.reload();
        }).fail(function () {
          Notify.danger(Translator.trans('admin.teacher.field.save_error'));
        });
      }
    });

    validator.addItem({
      element: '#profession-name-field',
      required: true,
      rule: 'chinese_alphanumeric byte_maxlength{max:30} remote'
    });
  }

});
