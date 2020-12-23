define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $('#removecache').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.optimize.remove_cache_success'));
            }).error(function(response){
                Notify.danger(Translator.trans('admin.optimize.remove_cache_error'));
            });
        });
        $('#removeTmp').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.optimize.remove_tmp_success'));
            }).error(function(response){
                Notify.danger(Translator.trans('admin.optimize.remove_tmp_error'));
            });
        });
        $('#removeBackup').on('click',  function() {
            if (!confirm(Translator.trans('admin.optimize.remove_backup_confirm_message'))) return false;
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.optimize.remove_backup_success'));
            }).error(function(response){
                Notify.danger(Translator.trans('admin.optimize.remove_backup_error'));
            });
        });
        $('#backupDatabase').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                if(response.status=='ok'){
                    Notify.success(Translator.trans('admin.optimize.remove_database_confirm_message'));
                    $('#dbbackup').removeClass('hide');
                    $('#dbdownload').attr('href',response.result);
                }
            }).error(function(response){
                Notify.danger(Translator.trans('admin.optimize.remove_database_error'));
            });
        });



    };

});
