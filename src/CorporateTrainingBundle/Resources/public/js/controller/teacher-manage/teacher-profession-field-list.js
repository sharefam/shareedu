define(function(require, exports, module) {
  require('jquery.sortable');
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {
    $('.delete-profession-field').click(function(){
      if (!confirm(Translator.trans('admin.teacher.field.delete_confirm_message'))) {
        return;
      };
      $.post($(this).data('url'), function(result){
        if (result.success) {
          Notify.success(Translator.trans(result.message));
          window.location.reload();
        } else {
          Notify.danger(Translator.trans(result.message));
        }
      });
    });
  }
});
