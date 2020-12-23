define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require('es-ckeditor');
    require("jquery.bootstrap-datetimepicker");

    exports.run = function() {

        // group: 'course'
        var editor = CKEDITOR.replace('about', {
            toolbar: 'Simple',
            language: document.documentElement.lang,
            filebrowserImageUploadUrl: $('#about').data('imageUploadUrl')
        });

        var $modal = $('#user-edit-form').parents('.modal');

        var validator = new Validator({
            element: '#user-edit-form',
            autoSubmit: false,
             failSilently: true,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#edit-user-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    console.log(html);
                    Notify.success(Translator.trans('admin.user.update_user_success'));
                    var $tr = $(html);
                    $('#' + $tr.attr('id')).replaceWith($tr);
                }).error(function(){
                    Notify.danger(Translator.trans('admin.user.update_user_error'));
                });
            }
        });

        (function initDateTimpPicker() {
            $("[name=hireDate]").datetimepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                minView: 'month',
            });
        })();

        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });


        validator.addItem({
            element: '[name="truename"]',
            required: true,
            rule: 'truename_chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:200}'
        });

        validator.addItem({
            element: '[name="qq"]',
            rule: 'qq'
        });

        validator.addItem({
            element: '[name="weibo"]',
            rule: 'url',
            errormessageUrl: Translator.trans('admin.user.weibo.message')
        });

        validator.addItem({
            element: '[name="site"]',
            rule: 'url',
            errormessageUrl: Translator.trans('admin.user.site.message')
        });

        validator.addItem({
            element: '[name="mobile"]',
            rule: 'phone'
        });

        validator.addItem({
            element: '[name="idcard"]',
            rule: 'idcard'
        });

        validator.addItem({
            element: '[name="hireDate"]',
            rule: 'date'
        });

        for(var i=1;i<=5;i++){
             validator.addItem({
             element: '[name="intField'+i+'"]',
             rule: 'int'
             });

             validator.addItem({
            element: '[name="floatField'+i+'"]',
            rule: 'float'
            });

             validator.addItem({
            element: '[name="dateField'+i+'"]',
            rule: 'date'
             });
        }

        };

});
