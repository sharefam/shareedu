define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var validator = require('bootstrap.validator');
    exports.run = function() {
        var $datePicker = $('#datePicker');
        var $table = $('#user-table');

        $table.on('click', '.lock-user, .unlock-user', function() {
            var $trigger = $(this);

            if (!confirm(Translator.trans('admin.user.list.confirm_message',{title:$trigger.attr('title')}))) {
                return;
            }

            $.post($(this).data('url'), function(html) {
                Notify.success(Translator.trans('admin.user.list.lock_success',{title:$trigger.attr('title')}));
                var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function() {
                Notify.danger(Translator.trans('admin.user.list.lock_error',{title:$trigger.attr('title')}));
            });
        });

        $table.on('click', '.send-passwordreset-email', function() {
            Notify.info(Translator.trans('admin.user.list.send_passwordreset_email.loading'), 60);
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.user.list.send_passwordreset_email.success'));
            }).error(function() {
                Notify.danger(Translator.trans('admin.user.list.send_passwordreset_email.error'));
            });
        });

        $table.on('click', '.send-emailverify-email', function() {
            Notify.info(Translator.trans('admin.user.list.send-emailverify-email.loading'), 60);
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.user.list.send-emailverify-email.success'));
            }).error(function() {
                Notify.danger(Translator.trans('admin.user.list.send-emailverify-email.error'));
            });
        });

        var $userSearchForm = $('#user-search-form');

        $('#user-export').on('click', function() {
            var self = $(this);
            var data = $userSearchForm.serialize();
            self.attr('data-url', self.attr('data-url') + "?" + data);
        });

        $("#startDate").datetimepicker({
            autoclose: true,
        }).on('changeDate', function() {
            $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
        });

        $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));

        $("#endDate").datetimepicker({
            autoclose: true,
        }).on('changeDate', function() {

            $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));
        });

        $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
    };

});
