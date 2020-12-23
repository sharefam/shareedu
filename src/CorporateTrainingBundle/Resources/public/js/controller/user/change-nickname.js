define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    var $modal = $('#change-nickname-form');

    var validator = new Validator({
      element: '#change-nickname-form',
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return false;
        }

        $('#change-nickname-btn').button('submiting').addClass('disabled');
        var $modal = $('#modal');
        $.post($form.attr('action'), $form.serialize(), function (html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.user.chang_nickname_success'));
          var $tr = $(html);
          $('#' + $tr.attr('id')).replaceWith($tr);
        }).error(function () {
          Notify.danger(Translator.trans('admin.user.chang_nickname_error'));
        });

      }
    });
    validator.addItem({
      element: '[name="newNickname"]',
      required: true,
      display: Translator.trans('student.profile.nickname'),
      rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:200} remote'
    });
  };

});
