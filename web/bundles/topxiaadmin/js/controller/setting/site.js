define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $form = $("#site-form");

      $('#save-site').on('click', function(){
        $.post($form.data('saveUrl'), $form.serialize(), function(data){
            Notify.success(data.message);
        })
      })
    };

});
