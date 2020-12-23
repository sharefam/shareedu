define(function (require, exports, module) {
  let initSwitch = require('topxiaadminbundle/controller/widget/switch');
  let Validator = require('bootstrap.validator');
  exports.run = function () {
    initSwitch();
  
    let validator = new Validator({
      element: '#mail-set-form',
      autoSubmit: true,
    });
  };
  
});