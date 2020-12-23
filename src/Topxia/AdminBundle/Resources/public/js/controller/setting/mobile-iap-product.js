define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $("[data-role=delete]").on('click', function(){
            if (!confirm(Translator.trans('admin.setting.mobile_iap_product.confirm_message'))) return false;
            $.post($(this).data('url'), function() {
                Notify.success(Translator.trans('admin.setting.mobile_iap_product.delete_success'));
                window.location.reload();
            }).error(function(){
                Notify.danger(Translator.trans('admin.setting.mobile_iap_product.delete_error'));
            });
        });

    };

});
