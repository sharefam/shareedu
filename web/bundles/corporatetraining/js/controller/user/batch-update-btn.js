define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  exports.run = function () {

    var initDom = function () {
      var formId = $('#batch-update-org').data('formId');
      var generate = $('#batch-update-org').data('generate');
      if (generate == false) {
        return;
      }
      $('#' + formId).find('thead tr').prepend('<th><input type="checkbox"  data-role="batch-select"></th>');
    }

    var getCheckstatus = function (ischeck) {
      var status = true;
      $("[data-role='batch-select']").parents('table').find("[data-role='batch-item']").each(function () {
        if ($(this).prop("checked") === ischeck) {
          status = false;
          return;
        }
      });
      return status;
    }

    initDom();

    $("[data-role='batch-select']").on('click', function () {
      if ($(this).prop('checked')) {
        $("[data-role='batch-select']").parents('table').find("[data-role='batch-item']").prop("checked", true);
        $("[data-role='batch-select']").prop("checked", true);
      } else {
        $("[data-role='batch-select']").parents('table').find("[data-role='batch-item']").prop("checked", false);
        $("[data-role='batch-select']").prop("checked", false);
      }
    });

    $("[data-role='batch-select']").parents('table').on('click', "[data-role='batch-item']", function () {
      $("[data-role='batch-select']").prop('checked', getCheckstatus(false));
    });

    $("#batch-update-org,#batch-update-post,#bach-lock-user").on('click', function (e) {
      if (getCheckstatus(true)) {
        Notify.warning(Translator.trans('admin.user.batch_update.select_empty'));
        e.stopImmediatePropagation();
      }
    });
  };
});
