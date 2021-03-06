define(function(require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {

    var validator = new Validator({
      element: '#login_bind-form',
    });

    validator.addItem({
      element: '[name=temporary_lock_allowed_times]',
      rule: 'integer'
    });

    validator.addItem({
      element: '[name=temporary_lock_minutes]',
      rule: 'integer'
    });

    var hideOrShowTimeAndMinutes = function() {
      if ($('[name=temporary_lock_enabled]').filter(':checked').attr("value") == 1) {
        $('#times_and_minutes').show();
      } else if ($('[name=temporary_lock_enabled]').filter(':checked').attr("value") == 0) {
        $('#times_and_minutes').hide();
      };
    };
    hideOrShowTimeAndMinutes();
    $('[name=temporary_lock_enabled]').change(function() {
      hideOrShowTimeAndMinutes();
    });

    $('[name=enabled]').change(function() {
      if ($('[name=enabled]').filter(':checked').attr("value") == 1) {
        $('#third_login').show();
        $('#onlyThirdPartyLogin').removeClass('hidden');
      } else if ($('[name=enabled]').filter(':checked').attr("value") == 0) {
        $('#third_login').hide();
        $('#onlyThirdPartyLogin').addClass('hidden');
        $('[name=only_third_party_login]').prop('checked',false);
      };
    });

    $('[name=dingtalkMode]').change(function() {
      if ($('[name=dingtalkMode]').filter(':checked').attr("value") == 'close') {
        $('#dingtalk_tips').hide();
        $('#dingtalk_app_setting').hide();
      } else if ($('[name=dingtalkMode]').filter(':checked').attr("value") == 'login') {
        $('#dingtalk_tips').show();
        $('#dingtalk_app_setting').show();
      }
    });

    $('[name=dingtalkMode]').change(function() {
      if ($(this).val() == 'close') {
        validator.removeItem('[name=dingtalkweb_key]');
        validator.removeItem('[name=dingtalkweb_secret]');
      } else if ($(this).val() == 'login') {
        validator.addItem({
          element: '[name=dingtalkweb_key]',
          required: true
        });
        validator.addItem({
          element: '[name=dingtalkweb_secret]',
          required: true
        });
      }
    });

    $('[data-role=oauth2-setting]').each(function() {
      var type = $(this).data('type');
      $('[name=' + type + '_enabled]').change(function() {
        if ($(this).val() == '1') {
          validator.addItem({
            element: '[name=' + type + '_key]',
            required: true
          });
          validator.addItem({
            element: '[name=' + type + '_secret]',
            required: true
          });
        } else {
          validator.removeItem('[name=' + type + '_key]');
          validator.removeItem('[name=' + type + '_secret]');
        }
      })

      $('[name=' + type + '_enabled]:checked').change();
    });

    $('#help').popover({
      html: true,
      container: "body",
      template: '<div class="popover help-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
    });

    $('[data-toggle="tooltip"]').tooltip();
  };
});
