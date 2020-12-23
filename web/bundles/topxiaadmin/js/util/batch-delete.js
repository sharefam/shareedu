define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

    module.exports = function($element) {
        
        $element.on('click', '[data-role=batch-delete]', function() {
            
        	var $btn = $(this);
        		name = $btn.data('name');

            var ids = [];
            $element.find('[data-role=batch-item]:checked').each(function(){
                ids.push(this.value);
            });

            if (ids.length == 0) {
                Notify.danger(Translator.trans('admin.util.empty',{name:name}));
                return ;
            }

            if (!confirm(Translator.trans('admin.util.batch_delete_confirm_message',{ids:ids.length,name:name}))) {
                return ;
            }

            $element.find('.btn').addClass('disabled');

            Notify.info(Translator.trans('admin.util.batch_delete_loading_message',{name:name}), 60);

            $.post($btn.data('url'), {ids:ids}, function(){
            	window.location.reload();
            });

        });

    };

});
