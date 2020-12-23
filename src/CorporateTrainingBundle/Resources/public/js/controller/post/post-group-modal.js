define(function (require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    var $form = $('#post-group-form');
    var $modal = $form.parents('.modal');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return;
        }

        $('#post-group-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize()).done(function (html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.post_group.submit_success'));
          window.location.reload();
        }).fail(function () {
          Notify.danger(Translator.trans('admin.post_group.submit_error'));
        });
      }
    });

    validator.addItem({
      element: '#post-group-name-field',
      required: true,
      rule: 'byte_maxlength{max:30} remote'
    });
  };
});
