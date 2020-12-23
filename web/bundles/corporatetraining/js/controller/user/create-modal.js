define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);
  require('jquery.bootstrap-datetimepicker');
  require('jquery.select2-css');
  require('jquery.select2');

  exports.run = function () {
    var $modal = $('#user-create-form').parents('.modal');
    $('#stu').prop('checked', 'true');
    var validator = new Validator({
      element: '#user-create-form',
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return false;
        }

        $('#user-create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function (html) {
          $modal.modal('hide');
          if (html.error) {
            Notify.danger(Translator.trans('admin.user.create_user_error')+ html.error);
          } else {
            Notify.success(Translator.trans('admin.user.create_user_success'));
            window.location.reload();
          }
        }).error(function (html) {
          Notify.danger(Translator.trans('admin.user.create_user_error'));
        });
      }
    });
    validator.addItem({
      element: '[name="email"]',
      required: true,
      rule: 'email email_remote'
    });

    validator.addItem({
      element: '#truename',
      required: true,
      rule: 'truename_chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:200}'
    });

    validator.addItem({
      element: '[name="nickname"]',
      required: true,
      rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:64} remote'
    });

    validator.addItem({
      element: '[name="password"]',
      required: true,
      rule: 'check_password'
    });

    validator.addItem({
      element: '[name="confirmPassword"]',
      required: true,
      rule: 'confirmation{target:#password}'
    });

    validator.addItem({
      element: '[name="orgCodes"]',
      required: true,
    });

    (function initDateTimpPicker() {
      $('[name=hireDate]').datetimepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        minView: 'month',
      });
    })();
  };
});
