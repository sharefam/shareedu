define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  exports.run = function () {
    var ids = [];
    $("[data-role='batch-item']:checked").each(function () {
      var id = $(this).parents('tr').attr('id');
      var userId = id.split('-').pop();
      if ($("#module").val() == "user" && userId == $("#appUserId").val()) {
        $(this).prop("checked", false);
        $(".js-user-help").removeClass("hidden");
        return;
      }
      ids.push(userId);
    });
    if (ids.length == 0) {
      $("#batch-setting-post-btn").addClass("disabled");
    }
    $("#batch-ids").val(ids);

    var $modal = $('#batch-setting-post-form').parents('.modal');
    var validator = new Validator({
      element: '#batch-setting-post-form',
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return false;
        }
        $('#batch-setting-post-btn').addClass('disabled');
        $.post($form.attr('action'), $form.serialize(), function (result) {
          if (result.message) {
            Notify.danger(Translator.trans(result.message));
            $('#batch-setting-post-btn').removeClass('disabled');
          } else {
            $modal.modal('hide');
            Notify.success(Translator.trans('admin.user.batch_update_post_success'));
            setTimeout(function () {
              window.location.reload();
            }, 1000);
          }
        }).error(function () {
          Notify.danger(Translator.trans('admin.user.batch_update_post_error'));
        });
      }
    });

  };

});
