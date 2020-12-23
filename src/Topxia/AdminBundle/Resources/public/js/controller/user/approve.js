define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		var $form = $('#approve-form');
		$('button[type=submit]').click(function() {

			var submitButton = $(this);
			var status = submitButton.data('status');

			if (status == 'fail'){
				var ret=confirm(Translator.trans('admin.user.approve.approve_success'));
				if (!ret) {
					return false;
				}
			}
			
			if (status == 'success'){
				var ret=confirm(Translator.trans('admin.user.approve.approve_confirm_message'));
				if (!ret) {
					return false;
				}
			}

			if (status == 'fail' && $('#note').val() == '') {
				Notify.danger(Translator.trans('admin.user.approve.approve_error'));
				return false;
			}

			if($("#note").val().length > 100){
				Notify.danger(Translator.trans('admin.user.approve.approve_note_max'));
				return false;
			}

			$('#form_status').val(status);
			$('.user-approve-btn').button('submiting').addClass('disabled');
			$.post($form.attr('action'), $form.serialize(), function(response){
				var originText = submitButton.text();
				submitButton.text(Translator.trans('admin.user.approve.submit_btn_loading'));
				$('button').attr('disabled', 'disabled');

				if (response.status == 'error') {
					Notify.danger(response.error.message);
					submitButton.text(originText);
					$('button').attr('disabled', false);
				} else {
					window.location.reload();
				}

			}, 'json');

			return false;

		});

	};

});
