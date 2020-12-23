define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  var Notify = require('common/bootstrap-notify');

  exports.run = function () {
    var validator = new Validator({
      element: '#password-reset-form'
    });

    validator.addItem({
      element: '[name="newPassword"]',
      required: true,
      rule: 'minlength{min:5} maxlength{max:20}'
    });

    validator.addItem({
      element: '[name="confirmPassword"]',
      required: true,
      rule: 'confirmation{target:#newPassword}'
    });

  };

});
