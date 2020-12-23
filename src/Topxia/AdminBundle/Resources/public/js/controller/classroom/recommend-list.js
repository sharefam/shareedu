define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function(options) {
        var $table=$('#classroom-table');

        $table.on('click', '.cancel-recommend-classroom', function() {          
            var $trigger = $(this);
            if (!confirm(Translator.trans('admin.classroom.list.cancel_recommend_confirm_message',{title:$trigger.attr('title')}))) {
                    return ;
                }
            $.post($(this).data('url'), function(html){
                    Notify.success(Translator.trans('admin.classroom.list.cancel_recommend_success',{title:$trigger.attr('title')}));
                     var $tr = $(html);
                    $('#' + $tr.attr('id')).remove();
                }).error(function(){
                    Notify.danger(Translator.trans('admin.classroom.list.cancel_recommend_error',{title:$trigger.attr('title')}));
                });

        });

    };

});
