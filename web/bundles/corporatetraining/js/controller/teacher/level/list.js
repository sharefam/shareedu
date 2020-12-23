define(function (require, exports) {
  var Notify = require('common/bootstrap-notify');

  exports.run = function () {

    let $table = $('#level-table');

    $table.on('click', '.delete-level', function() {
      if (!confirm(Translator.trans('admin.teacher.delete_level_confirm_message'))) return false;
      $.post($(this).data('url'), function(response) {
        if (!response.success) {
          Notify.danger(response.message);
        } else {
          Notify.success(Translator.trans('admin.teacher.delete_level_success'));
          window.location.reload();
        }
      }).error(function(e) {
        Notify.danger(Translator.trans('admin.teacher.delete_level_error'));
      });
    });
  };
});
