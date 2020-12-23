define(function (require, exports, module) {

  var Notify = require('common/bootstrap-notify');
  require('jquery.bootstrap-datetimepicker');
  require('jquery.select2');
  var validator = require('bootstrap.validator');
  exports.run = function () {
    var $datePicker = $('#datePicker');
    var $table = $('#user-table');

    $('#hireDate_GTE').datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 'month',
    }).on('changeDate', function () {
      $('#hireDate_LTE').datetimepicker('setStartDate', $('#hireDate_GTE').val().substring(0, 16));
    });

    $('#hireDate_GTE').datetimepicker('setEndDate', $('#hireDate_LTE').val().substring(0, 16));

    $('#hireDate_LTE').datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 'month',
    }).on('changeDate', function () {

      $('#hireDate_GTE').datetimepicker('setEndDate', $('#hireDate_LTE').val().substring(0, 16));
    });

    $('#hireDate_LTE').datetimepicker('setStartDate', $('#hireDate_GTE').val().substring(0, 16));


    $table.on('click', '.lock-user, .unlock-user', function () {
      let $trigger = $(this);

      if (!confirm(Translator.trans('admin.user.list.confirm_message', {title: $trigger.attr('title')}))) {
        return;
      }
      $.post($(this).data('url'), function (html) {
        if (html.error) {
          Notify.danger(Translator.trans('admin.user.list.operation_error', {title: $trigger.attr('title')}) + html.error);
        } else {
          Notify.success(Translator.trans('admin.user.list.operation_success', {title: $trigger.attr('title')}));
          let $tr = $(html);
          $('#' + $tr.attr('id')).replaceWith($tr);
        }
      }).error(function (html) {
        Notify.danger(Translator.trans('admin.user.list.operation_error', {title: $trigger.attr('title')}));
      });
    });

    $('#bach-lock-user').on('click', function () {
      let $trigger = $(this);

      if (!confirm(Translator.trans('admin.user.list.confirm_message', {title: $trigger.attr('title')}))) {
        return;
      }
      let ids = [];
      $("[data-role='batch-item']:checked").each(function () {
        let id = $(this).parents('tr').attr('id');
        let userId = id.split('-').pop();
        ids.push(userId);
      });
      if (ids.length === 0) {
        $('#bach-lock-user').addClass('disabled');
      }

      $.post($(this).data('url'), {ids: ids}, function (html) {
        if (html.error) {
          Notify.danger(Translator.trans('admin.user.list.operation_error', {title: $trigger.attr('title')})+ html.error);
        } else {
          Notify.success(Translator.trans('admin.user.list.operation_success', {title: $trigger.attr('title')}));
          window.location.reload();
        }
      }).error(function (html) {
        Notify.danger(Translator.trans('admin.user.list.operation_error', {title: $trigger.attr('title')}));
      });
    });

    $table.on('click', '.send-passwordreset-email', function () {
      Notify.info(Translator.trans('admin.user.list.send_passwordreset_email.info'), 60);
      $.post($(this).data('url'), function (response) {
        Notify.success(Translator.trans('admin.user.list.send_passwordreset_email.success'));
      }).error(function () {
        Notify.danger(Translator.trans('admin.user.list.send_passwordreset_email.error'));
      });
    });

    $table.on('click', '.send-emailverify-email', function () {
      Notify.info(Translator.trans('admin.user.list.send_emailverify_email.info'), 60);
      $.post($(this).data('url'), function (response) {
        Notify.success(Translator.trans('admin.user.list.send_emailverify_email.success'));
      }).error(function () {
        Notify.danger(Translator.trans('admin.user.list.send_emailverify_email.error'));
      });
    });

    var $userSearchForm = $('#user-search-form');

    $('#user-export').on('click', function () {
      var self = $(this);
      var data = $userSearchForm.serialize();
      self.attr('data-url', self.attr('data-url') + '?' + data);
    });

    var $postContainer = $('#postId');
    $postContainer.select2({
      ajax: {
        url: $postContainer.data('url'),
        dataType: 'json',
        quietMillis: 100,
        data: function (term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results: function (data) {
          var results = [];
          $.each(data, function (index, item) {

            results.push({
              id: item.id,
              name: item.name
            });
          });

          return {
            results: results
          };

        }
      },
      initSelection: function (element, callback) {
        var id = $(element).val();
        if (id !== "") {
          var name = $(element).data('name');
          callback({id: id, name: name});
        }
      },
      formatSelection: function (item) {
        return item.name;
      },
      formatResult: function (item) {
        return item.name;
      },
      formatSearching: function () {
        return Translator.trans('site.searching_hint');
      },
      formatNoMatches: function () {
        return Translator.trans('select.format_no_matches');
      },
      allowClear: true,
      width: 'off',
      placeholder: Translator.trans('admin.user.list.all_post')
    });

    new window.$.CheckTreeviewInput({
      $elem: $('#user-orgCode'),
      saveColumn: 'orgCode',
      selectType: 'single'
    });
  };

});
