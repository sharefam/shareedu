define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $table = $('#teacher-table');

        $table.on('click', '.cancel-promote-teacher', function() {
            if (!confirm(Translator.trans('admin.teacher.pomote_list.cancel_promote_confirm_message'))) {
                return;
            }

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function(html) {
                Notify.success(Translator.trans('admin.teacher.pomote_list.cancel_promote_success'));
                var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            });

        });

    };

});
