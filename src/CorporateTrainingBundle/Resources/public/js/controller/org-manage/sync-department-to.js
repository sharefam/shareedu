define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);
  
  exports.run = function () {
    var $form = $('#sync-department-to-form');
    var $modal = $form.parents('.modal');
    
    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return;
        }
        $('#sync-department-to-btn').button('submiting').addClass('disabled');
        
        var url = $('[name=sync-type]').filter(':checked').data("url");
        
        $.post(url, function (result) {
          if (result.success) {
            $modal.modal('hide');
            Notify.success(result.message);
            window.location.reload();
          } else {
            Notify.danger(result.message);
          }
        }).fail(function () {
          Notify.danger(Translator.trans('admin.org.sync_error'));
        });
        
      }
    });
    
  }
  
});
