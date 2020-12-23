define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  require("placeholder");
  require("jquery.bootstrap-datetimepicker");
  exports.run = function () {


    var validator = new Validator({
      element: '#login-form'
    });
    validator.addItem({
      element: '[name="_username"]',
      required: true,
      display: Translator.trans('admin.auth_username')
    });

    validator.addItem({
      element: '[name="_password"]',
      required: true,
      display: Translator.trans('admin.auth_password')
    });
    $('.receive-modal').click();

  };

});
