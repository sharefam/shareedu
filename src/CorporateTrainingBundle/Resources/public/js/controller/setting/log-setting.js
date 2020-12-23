define(function(require, exports, module) {

    var WebUploader = require('edusoho.webuploader');
    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');

    exports.run = function() {
        var $form = $("#log-form");
        var uploader = new WebUploader({
            element: '#site-logo-upload'
        });

        uploader.on('uploadSuccess', function(file, response ) {
            var url = $("#site-logo-upload").data("gotoUrl");

            $.post(url, response ,function(data){
                $("#site-logo-container").html('<img src="' + data.url + '">');
                $form.find('[name=logo]').val(data.path);
                $("#site-logo-remove").show();
                Notify.success(Translator.trans('admin.setting.site.logo_setting_success'));
            });
        });

        $("#site-logo-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.setting.site.delete_confirm_message'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#site-logo-container").html('');
                $form.find('[name=logo]').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.setting.site.logo_delete_success'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.setting.site.logo_delete_error'));
            });
        });

        var uploader1 = new WebUploader({
            element: '#site-favicon-upload'
        });

        uploader1.on('uploadSuccess', function(file, response ) {
            var url = $("#site-favicon-upload").data("gotoUrl");

            $.post(url, response ,function(data){
                $("#site-favicon-container").html('<img src="' + data.url + '" style="margin-bottom: 10px;">');
                $form.find('[name=favicon]').val(data.path);
                $("#site-favicon-remove").show();
                Notify.success(Translator.trans('admin.setting.site.favicon_setting_success'));
            });
        });

        $("#site-favicon-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.setting.site.delete_confirm_message'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#site-favicon-container").html('');
                $form.find('[name=favicon]').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.setting.site.favicon_delete_success'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.setting.site.favicon_delete_error'));
            });
        });

        var uploaderLogin = new WebUploader({
            element: '#login-logo-upload'
        });

        uploaderLogin.on('uploadSuccess', function(file, response ) {
            var url = $("#login-logo-upload").data("gotoUrl");

            $.post(url, response ,function(data){
                $("#login-logo-container").html('<img src="' + data.url + '">');
                $form.find('[name=loginLogo]').val(data.path);
                $("#login-logo-remove").show();
                Notify.success(Translator.trans('admin.setting.site.login.logo_setting_success'));
            });
        });

        $("#login-logo-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.setting.site.delete_confirm_message'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#login-logo-container").html('');
                $form.find('[name=loginLogo]').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.setting.site.login.logo_delete_success'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.setting.site.login.logo_delete_error'));
            });
        });
        $('#save-log').on('click', function(){
            $.post($form.data('saveUrl'), $form.serialize(), function(data){
                Notify.success(data.message);
            })
        })
    };

});
