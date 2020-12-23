define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $table = $('#classroom-table');

        $('.data-list').on('click', '.close-classroom,.open-classroom,.cancel-recommend-classroom', function() {
            var $trigger = $(this);
            if (!confirm($trigger.attr('title') + Translator.trans('吗？'))) {
                return;
            }
            $.post($(this).data('url'), function(html) {
                Notify.success(Translator.trans('admin.classroom.list.cancel_recommend_success',{title:$trigger.attr('title')}));
                var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function() {
                Notify.danger(Translator.trans('admin.classroom.list.cancel_recommend_error',{title:$trigger.attr('title')}));
            });

        });


        $('.data-list').on('click', '.delete-classroom' ,function() {
            if (!confirm(Translator.trans('admin.classroom.delete_confirm_message'))) {
                return;
            }
            $.post($(this).data('url'), function() {
                Notify.success(Translator.trans('admin.classroom.delete_success'));
                window.location.reload();
            });
        });

    }

});
