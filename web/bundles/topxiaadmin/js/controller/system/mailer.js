define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('.js-self-test').on('click', function () {
            var $this = $(this);
            $this.text(Translator.trans('admin.system.mailer.check_loading'));
            $.get($this.data('url')).done(function (response) {
                if(response.status){
                    Notify.success(Translator.trans('admin.system.mailer.send_success'), 3);
                }else {
                    Notify.danger(Translator.trans('admin.system.mailer.send_error'), 3);
                }
                $this.text(Translator.trans('admin.system.mailer.check'));
            });
        });

        if($("input[name='email-setting-status']").val()=="email"){
            $('#mailer-form').show();
        }
        var validator = new Validator({
            element: '#mailer-form'
        });

        if($("input[name='email-setting-status']").val()=="cloud_email_crm"){
            validator.addItem({
                element: '[name="name"]',
                required: true,
                errormessageRequired: Translator.trans('admin.system.mailer.email_setting.name')
            });
        }

        $('[name=enabled]').change(function(e) {
            var radio = e.target.value;

            if (radio == '1') {
                validator.addItem({
                    element: '[name="host"]',
                    required: true,
                    errormessageRequired: Translator.trans('admin.system.mailer.host')
                });
                validator.addItem({
                    element: '[name="port"]',
                    required: true,
                    rule:'integer',
                    errormessageRequired: Translator.trans('admin.system.mailer.port')
                });
                validator.addItem({
                    element: '[name="username"]',
                    required: true,
                    rule: 'email',
                    errormessageRequired: Translator.trans('admin.system.mailer.username')
                });
                validator.addItem({
                    element: '[name="password"]',
                    required: true,
                    rule:'password',
                    errormessageRequired: Translator.trans('admin.system.mailer.password')
                });
                validator.addItem({
                    element: '[name="from"]',
                    required: true,
                    rule: 'email',
                    errormessageRequired: Translator.trans('admin.system.mailer.from')
                });
                validator.addItem({
                    element: '[name="name"]',
                    required: true,
                    errormessageRequired: Translator.trans('admin.system.mailer.name')
                });
            } else {
                if (app.arguments.registerEmailVerified == 'opened') {
                    var emailSetUrl = $('#mailer-form').data('userSetting');
                    Notify.danger('admin.system.mailer.register_email',{emailSetUrl: emailSetUrl});
                    
                    $('[name=enabled][value="0"]').prop('checked',false);
                    $('[name=enabled][value="1"]').prop('checked',true);
                    return;
                }
                validator.removeItem('[name="host"]');
                validator.removeItem('[name="port"]');
                validator.removeItem('[name="username"]');
                validator.removeItem('[name="password"]');
                validator.removeItem('[name="from"]');
                validator.removeItem('[name="name"]');
            }
        });
        
        $('input[name="enabled"]:checked').change();
        $("#email").click(function(){
            $('#email-status').hide();
            $('#mailer-form').show();
        });
    };

});
