define(function (require, exports, module) {
  'use strict';
  let Backbone = require('backbone');
  let _ = require('underscore');
  let ProgressView = require('./progress');

  module.exports = Backbone.View.extend({
    template: _.template(require('./../template/confirm.html')),

    events: {
      'click #import-new-btn': 'onImportNew',
      'click #import-all-btn': 'onImportAll',
    },

    initialize: function () {
      this.$el.html(this.template(this.model.toJSON()));
    },

    _import: function () {
      this.progress = new ProgressView({
        model: this.model
      });

      let $modal = $('#modal');
      $modal.html(this.progress.el);
      $modal.modal({
        show: true,
        backdrop: 'static',
        keyboard: false
      });

      this.model.chunkImport();
    },

    onImportNew: function () {
      this.model.set('checkType', 'ignore');
      this._import();
    },

    onImportAll: function () {
      this.model.set('checkType', 'update');
      this._import();
    },
  });
});