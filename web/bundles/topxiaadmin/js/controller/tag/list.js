define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	exports.run = function() {
		$('body').on('click', 'button.delete-btn', function() {
			if (!confirm(Translator.trans('admin.tag.delete_confirm_message'))) return false;
			var $btn = $(this);
			$.post($btn.data('url'), function(response) {
				if (response.status == 'ok') {
					$('#' + $btn.data('target')).remove();
					Notify.success(Translator.trans('admin.tag.delete_success'));
				} else {
					alert(Translator.trans('admin.tag.delete_error'));
				}
			}, 'json');
		});
	};

});
