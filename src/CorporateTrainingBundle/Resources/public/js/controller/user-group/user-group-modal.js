define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    var $form = $('#user-group-form');
    var $modal = $form.parents('.modal');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return;
        }

        $('#user-group-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize()).done(function (html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.user_group.submit_success'));
          window.location.reload();
        }).fail(function () {
          Notify.danger(Translator.trans('admin.user_group.submit_error'));
        });
      }
    });

    $('.delete-user-group').on('click', function () {
      if (!confirm(Translator.trans('admin.user_group.delete_confirm_message'))) {
        return;
      }
      var id = $(this).data('id');
      var groupId = $(this).data('groupId');
      var parent = $('#' + id).parent();
      $.post($(this).data('url'), function (result) {
        if (result.success) {
          Notify.success(result.message);
          $('#' + id).remove();
        } else {
          Notify.danger(result.message);
        }
        $modal.modal('hide');
        window.location.reload();
      }).fail(function () {
        Notify.danger(Translator.trans('admin.user_group.delete_error'));
      });
    });

    validator.addItem({
      element: '#group-name-field',
      required: true,
      rule: 'chinese_alphanumeric byte_maxlength{max:30} remote'
    });

    validator.addItem({
      element: '#group-code-field',
      required: true,
      rule: 'alpha_numerica byte_maxlength{max:30} remote'
    });

    validator.addItem({
      element: '#group-description-field',
      rule: 'byte_maxlength{max:60}'
    });
  };

  Validator.addRule('alpha_numerica', /^([0-9]|[a-zA-Z])*$/i, `{{display}}${Translator.trans('admin.user_group.alpha_numerica_message')}`);

});
