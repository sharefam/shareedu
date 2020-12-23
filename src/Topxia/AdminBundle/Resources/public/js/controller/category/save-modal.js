define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    
	exports.run = function() {
        var $form = $('#category-form');
		var $modal = $form.parents('.modal');
        var $table = $('#category-table');

        $("#category-icon-delete").on('click', function(){
            if (!confirm(Translator.trans('admin.category.icon.delete_confirm_message'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#category-icon-field").html('');
                $form.find('[name=icon]').val('');
                $btn.hide();
                $('#category-icon-field').removeClass('mbm');
                Notify.success(Translator.trans('admin.category.icon.delete_success'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.category.icon.delete_error'));
            });
        });

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#category-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize()).done(function(html) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('admin.category.create_success'));
                    // $table.find('tbody').replaceWith(html);
                    window.location.reload();
				}).fail(function() {
                    Notify.danger(Translator.trans('admin.category.create_error'));
                });

            }
        });

        validator.addItem({
            element: '#category-name-field',
            required: true,
            rule: 'maxlength{max:100}'
        });

        validator.addItem({
            element: '#category-code-field',
            required: true,
            rule: 'alphanumeric not_all_digital remote'
        });

        $modal.find('.delete-category').on('click', function() {
            if (!confirm(Translator.trans('admin.category.delete_confirm_message'))) {
                return ;
            }

            $.post($(this).data('url'), function(html) {
                $modal.modal('hide');
                window.location.reload();
                // $table.find('tbody').replaceWith(html);
            });

        });

	};

});
