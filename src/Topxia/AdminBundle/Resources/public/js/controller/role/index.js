define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
    	$('.js-delete-role').click(function(){
    		var url = $(this).data('url');

			if(!confirm(Translator.trans('admin.role.delete_confirm_message'))){
				return;
			}

    		$.post(url).done(function(){
				Notify.success(Translator.trans('admin.role.delete_success'));
				document.location.reload();
			}).fail(function (error) {
				Notify.danger(Translator.trans('admin.role.delete_error'));
			});
    	})

    }
})
