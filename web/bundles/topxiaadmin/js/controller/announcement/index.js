define(function(require, exports, module) {
    exports.run = function() {
        $('#announcement-table').on('click', '.delete-btn', function() {

            if (!confirm(Translator.trans('admin.announcement.delete_confirm_message'))) {
                return;
            }

            $.post($(this).data('url'), function() {

                window.location.reload();

            });
        });

    };

});
