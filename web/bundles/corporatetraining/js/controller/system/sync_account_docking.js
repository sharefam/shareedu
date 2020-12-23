define(function(require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {

    var $form = $('#sync_account_docking-form');
    var $mode = $('.model');
    var validator = new Validator({
      element: '#sync_account_docking-form',
    });

    var addDingTalkEnabledValidate = function () {
      validator.addItem({
        element: '#dingtalksync_agentId',
        required: true
      });
      validator.addItem({
        element: '#dingtalkweb_key',
        required: true
      });
      validator.addItem({
        element: '#dingtalkweb_secret',
        required: true
      });
      validator.addItem({
        element: '#dingtalksync_key',
        required: true
      });
      validator.addItem({
        element: '#dingtalksync_secret',
        required: true
      });
    };
    
    if ($form.data('mode') == 'dingtalk') {
      addDingTalkEnabledValidate();
    }

    $mode.on('click', function() {
      $mode.removeClass("btn-primary");
      $(this).addClass("btn-primary");
      var modle = $(this).data('modle');

      $('[name="sync_mode"]').val(modle);
      if (modle == 'dingtalk') {
        $('.dingtalk-content').removeClass('hidden');
        addDingTalkEnabledValidate();
      } else {
        $('.dingtalk-content').addClass('hidden');
        removeDingTalkEnabledValidate();
      }
    });

    var removeDingTalkEnabledValidate = function () {
      validator.removeItem('#dingtalksync_agentId');
      validator.removeItem('#dingtalkweb_key');
      validator.removeItem('#dingtalkweb_secret');
      validator.removeItem('#dingtalksync_key');
      validator.removeItem('#dingtalksync_secret');
    };

    $('.js-check-btn').click(function (event) {
      $.post($('.js-check-btn').data('url'),$form.serialize(), function($result){
        if($result.status === 'error'){
          Notify.danger($result.message);
        }else{
          Notify.success($result.message);
        }

      });
    })

  }
});