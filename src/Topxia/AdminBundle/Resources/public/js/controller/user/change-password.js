define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $form = $("#change-password-form");

        var validator = new Validator({
            element: '#change-password-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }
                $('#change-password-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(html){
        
                    var $modal = $('#modal');

                    $.post($form.attr('action'), $form.serialize(), function(html) {
                        $modal.modal('hide');
                        Notify.success(Translator.trans('admin.user.change_password.change_success'));
                    }).error(function(){
                        Notify.danger(Translator.trans('admin.user.change_password.change_error'));
                    });
                });
            }
        });

        validator.addItem({
            element: '[name="newPassword"]',
            required: true,
            rule: 'check_password'
        });

        validator.addItem({
            element: '[name="confirmPassword"]',
            required: true,
            rule: 'confirmation{target:#newPassword}'
        });
    };
});
