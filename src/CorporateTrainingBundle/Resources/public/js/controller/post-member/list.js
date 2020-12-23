define(function (require, exports, module) {

  var Notify = require('common/bootstrap-notify');
  require('jquery.bootstrap-datetimepicker');

  exports.run = function () {
    var $container = $('#post-member-table-container');
    var $table = $('#post-member-table');
    require('../common/batch-select')($container);
    require('./batch-set-post.js')($container);
  };

});
