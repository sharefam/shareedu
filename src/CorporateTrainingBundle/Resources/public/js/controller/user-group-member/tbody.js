define(function (require, exports, module) {

  require('jquery.sortable');
  var Notify = require('common/bootstrap-notify');
  exports.run = function () {

    $('.js-delete-user-group-member').on('click', function () {
      if (!confirm(Translator.trans('admin.user_group_member.delete_confirm_message'))) {
        return;
      }
      var id = $(this).data('id');
      var parent = $('#' + id).parent();
      $.post($(this).data('url'), function (result) {
        if (result.success) {
          Notify.success(result.message);
          $('#' + id).remove();
        } else {
          Notify.danger(result.message);
        }
      }).fail(function () {
        Notify.danger(Translator.trans('admin.user_group_member.delete_error_message'));
      });
    });
  };
});
