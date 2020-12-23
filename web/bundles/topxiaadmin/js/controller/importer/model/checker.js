define(function (require, exports, module) {
  let Backbone = require('backbone');

  let Checker = Backbone.Model.extend({
    defaults: {
      'rule': 'ignore',
    }
  });

  module.exports = Checker;
});
