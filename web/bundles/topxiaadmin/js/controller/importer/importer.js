define(function (require, exports, module) {
  let Backbone = require('backbone');
  let Step1View = require('./view/step1');
  let Checker = require('./model/checker');
  let Importer = require('./model/importer');

  let Step2ErrorView = require('./view/step2-error.js');
  let Step2SuccessView = require('./view/step2-success.js');

  let _ = require('underscore');

  let ImporterApp = Backbone.Router.extend({
    routes: {
      'index': 'index',
      'error': 'error',
      'success': 'success'
    },

    index: function () {
      let checker = new Checker(this.options);
      let ste1View = new Step1View({
        'model': checker
      });
      this.$el.html(ste1View.el);
    },

    error: function () {

      if(_.isEmpty(this.errorData)){
        return;
      }

      let errorView = new Step2ErrorView(this.errorData);
      this.$el.html(errorView.el);

      this.errorData = undefined;
    },

    success: function () {
      if(this.successData === undefined){
        return;
      }

      let importer = new Importer(this.successData);
      importer.set('type', this.options.type);
      importer.set('importUrl', this.options.importUrl);
      let successView = new Step2SuccessView({
        model: importer
      });
      this.$el.html(successView.el);
      this.successData = undefined;
    },

    destroy: function () {
      Backbone.off('step2-error');
      Backbone.off('step2-success');
    },

    initialize: function(options) {
      this.options = options;
      this.$el = $(options.element);
      this._initEvent();
      Backbone.history.start();
      this.navigate('index', {trigger: true});
    },

    _initEvent: function () {
      let self = this;

      Backbone.on('step2-error', function (data) {
        self.errorData = data.errorInfo;
        self.navigate('error', {trigger: true});
      });

      Backbone.on('step2-success', function (data) {
        self.successData = data;
        self.navigate('success', {trigger: true});
      });
    }
  });

  exports.run = function () {
    let id = '#importer-app';
    let importer = new ImporterApp({
      element: '#importer-app',
      type: $(id).data('type'),
      checkUrl: $(id).data('checkUrl'),
      importUrl: $(id).data('importUrl'),
      template: $('#importer-template').html()
    });

    $('#modal').on('hide.bs.modal', function () {
      Backbone.history.stop();
      importer.destroy();
    });
  };
});
