define(function (require, exports, module) {

  exports.run = function () {

    $("#theme-table").on('click', '.use-theme-btn', function () {
      var $btn = $(this);

      if ($btn.data('protocol') < 3) {
        alert(Translator.trans('admin.setting.theme.error_message'));
        return false;
      } else if (confirm(Translator.trans('admin.setting.theme.confirm_message'))) {

        $.post($(this).data('url'), function (response) {
          if (response === false) {
            alert(Translator.trans('admin.setting.theme.error_message'));
          } else {
            window.location.reload();
          }
        });
      }
    });

  }

});
