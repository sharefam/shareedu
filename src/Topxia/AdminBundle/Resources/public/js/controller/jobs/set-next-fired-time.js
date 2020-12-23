define(function(require, exports, module){

  require('jquery.bootstrap-datetimepicker');
  var Notify = require('common/bootstrap-notify');
  exports.run = function(){

    $('#next-time-field').datetimepicker({
      language: 'zh-CN',
      autoclose: true,
      format: 'yyyy-mm-dd hh:ii',
      minView: 0,
      // startView: 1,
    });

    $('#next-time-field').datetimepicker('setStartDate', $('#next-time-field').data('now'));

    var $form = $('#set-next-fire-time-form');

    $form.submit(function() {
      $('#set-exec-time-btn').button('submiting').addClass('disabled');
      $.post($form.attr('action'), $form.serialize(), function(response) {
        if (response == true) {
          Notify.success(Translator.trans('site.modify.success'),1);
          window.location.reload();
        }else {
          Notify.warning(Translator.trans('site.save.fail'),1);
          $('#set-exec-time-btn').button('reset').removeClass('disabled');
        }
      }, 'json');
      return false;
    });
  };
});
