define(function (require, exports, module) {
  let Backbone = require('backbone');
  let _ = require('underscore');

  module.exports = Backbone.View.extend({
    template: _.template(require('./../template/error.html')),

    initialize: function (errors) {
      this.$el.html(this.template({errors: errors}));
    }
  });
});
