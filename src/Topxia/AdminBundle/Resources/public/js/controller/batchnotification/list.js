define(function(require, exports, module) {

	exports.run = function() {
		$("#batchnotification-table").on('click', '[data-role=publish-item]', function(){
			if (!confirm(Translator.trans('admin.batchnotification.publish_confirm_message'))) {
				return ;
			}
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});	

		$("#batchnotification-table").on('click', '[data-role=delete-item]', function(){
			if (!confirm(Translator.trans('admin.batchnotification.delete_confirm_message'))) {
				return ;
			}
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});
	};
});
