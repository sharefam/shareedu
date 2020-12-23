define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  require('jquery.select2-css');
  require('jquery.select2');

  exports.run = function () {
    var $modal = $('#change-post-form').parents('.modal');
    var validator = new Validator({
      element: '#change-post-form',
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return false;
        }

        $('#change-post-btn').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function (html) {
          if (html.message) {
            Notify.danger(Translator.trans(html.message));
            $('#change-post-btn').removeClass('disabled');
          } else {
            $modal.modal('hide');
            Notify.success(Translator.trans('admin.user.change_post_success'));
            var $tr = $(html);
            $('#' + $tr.attr('id')).replaceWith($tr);
          }

        }).error(function (e) {
          Notify.danger(Translator.trans('admin.user.change_post_error'));
        });

      }
    });

  };

});
