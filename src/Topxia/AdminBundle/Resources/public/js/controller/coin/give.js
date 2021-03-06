define(function(require, exports, module) {
    
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var $modal = $('#give-coin-form').parents('.modal');
        
        var validator = new Validator({
            element: '#give-coin-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form){
                if (error) {
                    return false;
                }

                $('#create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('admin.coin.submit_success'));
                    window.location.reload();
                }).error(function(){
                    Notify.danger(Translator.trans('admin.coin.submit_error'));
                });

            }
        });

        validator.addItem({
            element: '[name="amount"]',
            required: true,
            rule: 'positive_integer' 
        });

        validator.addItem({
            element: '#nickname',
            required: true,
            rule: 'chinese_alphanumeric remote'
        });
    };

    
});
