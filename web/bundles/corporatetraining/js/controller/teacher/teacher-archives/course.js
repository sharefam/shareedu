define(function (require, exports, module) {

  var OverviewDateRangePicker = require('../date-range-picker');
  exports.run = function () {
    this.dateRangePicker = new OverviewDateRangePicker();
  };
});
