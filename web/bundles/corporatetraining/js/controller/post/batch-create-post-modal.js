define(function (require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);
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

    Validator.addRule('name_max', function (options) {
      let maxLength = true;
      let values = $(options.element).val().split("\n");
      values.map(function (value, index, array) {
        let l = calculateByteLength(value);
        if(l > 200){
          maxLength = false;
        }
      });
      return maxLength;
    }, Translator.trans('admin.post.name_max_message'));

    validator.addItem({
      element: '#post-names',
      required: true,
      rule: 'name_max remote'
    });

    function calculateByteLength(string) {
      let length = string.length;
      for (let i = 0; i < string.length; i++) {
        if (string.charCodeAt(i) > 127)
          length++;
      }
      return length;
    }

  };
});
