define(function (require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);
  require('../widget/category-select');
  require('es-ckeditor');
  exports.run = function () {
    var $form = $('#post-form');
    var $modal = $form.parents('.modal');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return;
        }

        $('#post-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize()).done(function (html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.post.submit_success'));
          window.location.reload();
        }).fail(function () {
          Notify.danger(Translator.trans('admin.post.submit_error'));
        });
      }
    });

    validator.addItem({
      element: '#post-name-field',
      required: true,
      rule: 'byte_maxlength{max:200} remote'
    });

    validator.addItem({
      element: '#post-code-field',
      required: true,
      rule: 'remote maxlength{max:30}'
    });

  };
});
