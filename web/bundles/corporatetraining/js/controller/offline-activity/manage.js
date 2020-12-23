define(function (require, exports) {
    var Notify = require('common/bootstrap-notify');
    require('../widget/category-select').run('course');

    exports.run = function () {
        var $table = $('#offline-activity-table');
        
        $table.on('click', '.close-offline-activity', function () {
            var user_name = $(this).data('user');
            if (!confirm(Translator.trans('admin.offline_activity.close_sure_hint'))) return false;
            $.post($(this).data('url'), function (html) {
                var $tr = $(html);
                $table.find('#' + $tr.attr('id')).replaceWith(html);
                Notify.success(Translator.trans('admin.offline_activity.close_success_hint'));
            });
        });

        $table.on('click', '.publish-offline-activity', function() {
            var studentNum = $(this).closest('tr').next().val();
            if (!confirm(Translator.trans('admin.offline_activity.publish_sure_hint'))) return false;
            $.post($(this).data('url'), function(response) {
                console.log(response);
                if (!response['success']) {
                    Notify.danger(response['message']);
                } else {
                    var $tr = $(response.message);
                    $table.find('#' + $tr.attr('id')).replaceWith(response.message);
                    Notify.success(Translator.trans('admin.offline_activity.publish_success_hint'));
                }
            }).error(function(e) {
                var res = e.responseJSON.error.message || Translator.trans('admin.offline_activity.error.undefined_error_hint');
                Notify.danger(res);
            })
        });
        $('.js-data-popover').popover({
          html: true,
          trigger: 'hover',
          placement: 'bottom',
          template: '<div class="popover tata-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
          content: function() {

              var html = $(this).siblings('.popover-content').html();
              return html;
          }
        });
    };

});
