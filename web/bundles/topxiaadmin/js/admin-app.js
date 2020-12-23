define(function(require, exports, module) {
  require('./util/mark-menu.js');
  require('placeholder');
  require('common/bootstrap-modal-hack2');

  exports.load = function(name) {
    if (window.app.jsPaths[name.split('/', 1)[0]] === undefined) {
      name = window.app.basePath + '/bundles/topxiaadmin/js/controller/' + name;
    }

    seajs.use(name, function(module) {
      if ($.isFunction(module.run)) {
        module.run();
      }
    });

  };

  exports.loadScript = function(scripts) {
    for(var index in scripts) {
      exports.load(scripts[index]);
    }

  }

  window.app.load = exports.load;

  if (app.controller) {
    exports.load(app.controller);
  }

  if (app.scripts) {
    exports.loadScript(app.scripts);
  }

  if (app.scheduleCrontab) {
    $.post(app.scheduleCrontab);
  }	

});
