define(function (require, exports, module) {
  let Backbone = require('backbone');
  let _ = require('underscore');
  let ProgressView = require('./progress');
  let Step2ConfirmView = require('./step2-confirm');

  module.exports = Backbone.View.extend({
    template: _.template(require('./../template/success.html')),

    events: {
      'click #start-import-btn': 'onStartImport',
    },

    initialize: function () {
      this.$el.html(this.template(this.model.toJSON()));
    },

    onStartImport: function (event) {
      if (this.model.get('needConfirm')) {
        this.view = new Step2ConfirmView({
          model: this.model
        });
        $('#importer-app').html(this.view.el);
      } else {
        this.view = new ProgressView({
          model: this.model
        });
        let $modal = $('#modal');
        $modal.html(this.view.el);
        $modal.modal({
          show: true,
          backdrop: 'static',
          keyboard: false
        });
        this.model.chunkImport();
      }
    }
  });
});
