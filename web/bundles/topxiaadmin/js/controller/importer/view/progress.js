define(function (require, exports, module) {
  'use strict';
  let Backbone = require('backbone');
  let _ = require('underscore');

  let ProgressView = Backbone.View.extend({
    template: _.template(require('./../template/progress.html')),

    events: {
      'click .js-finish-import-btn': '_onFinishImport'
    },

    initialize: function () {
      this.$el.html(this.template());
      this.listenTo(this.model, 'change', this._onChange);
    },

    _capitalize: function (str) {
      return str.charAt(0).toUpperCase() + str.substr(1);
    },

    _onChange: function (model) {
      let on = '_on' + this._capitalize(model.get('__status'));
      if(ProgressView.prototype.hasOwnProperty(on)){
        this[on](model);
      }
    },

    _onProgress: function (model) {
      let progress = model.get('__progress') + '%';
      this.$el.find('.progress-bar-success').css('width', progress);
      this.$el.find('.progress-text').text(Translator.trans('admin.importer.view.progress.onprogress_text') + model.get('__quantity'));
      this.$el.find('.js-import-progress-text').removeClass('hidden');
    },

    _onComplete: function (model) {
      this.$el.find('.progress-bar').css('width', '100%');
      this.$el.find('a').removeClass('hidden');
      if(model.get('checkType')){
        let $importInfo = model.get('importInfo');
        if ('ignore' === model.get('checkType')) {
          let importUserCount = $importInfo ? $importInfo.importUserCount: model.get('__quantity');
          this.$el.find('.progress-text').text(Translator.trans('admin.importer.view.progress.oncomplete_text') + importUserCount);
        } else {
          this.$el.find('.progress-text').text(Translator.trans('admin.importer.view.progress.oncomplete_update_text', {importNum:$importInfo.importUserCount, updateNum:$importInfo.existUserCount}));
        }
        if($importInfo && $importInfo.failUserCount > 0){
          this.$el.find('.progress-text').removeClass('text-success');
          this.$el.find('.progress-text').addClass('text-warning');
          this.$el.find('.progress-text').append(Translator.trans('admin.importer.view.progress.fail_text', {failNum:$importInfo.failUserCount}));
        }
      }else {
        this.$el.find('.progress-text').text(Translator.trans('admin.importer.view.progress.oncomplete_text') + model.get('__quantity'));
      }

      this.$el.find('.js-import-progress-text').addClass('hidden');
    },

    _onError: function (model) {
      this.stopListening(this.model, 'change');
      this.$el.find('.progress-bar').css('width', '100%')
        .removeClass('progress-bar-success')
        .addClass('progress-bar-danger')
      ;
      this.$el.find('.progress-text').text(Translator.trans('admin.importer.view.progress.onerror_text')).removeClass('text-success').addClass('text-danger');
      this.$el.find('a').removeClass('hidden').text(Translator.trans('admin.importer.template.error.reimport_btn'));
    },

    _onFinishImport: function (event) {
      window.location.reload();
    }
  });

  module.exports = ProgressView;
});
