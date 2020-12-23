define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    exports.run = function(options) {

        var $table = $('#teacher-promote-table');

        $table.on('click', '.cancel-promote-teacher', function() {
            if (!confirm(Translator.trans('admin.teacher.pomote_list.cancel_promote_confirm_message'))) {
                return;
            }

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function() {
                Notify.success(Translator.trans('admin.teacher.pomote_list.cancel_promote_success'));
                $tr.remove();
            });
        });
        new window.$.CheckTreeviewInput({
          $elem: $('#orgCode'),
          selectType: 'single',
        });
    };

});
