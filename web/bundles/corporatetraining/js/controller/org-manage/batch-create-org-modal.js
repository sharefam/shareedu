define(function (require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    require('es-ckeditor');
    exports.run = function () {
        var $form = $('#org-form');
        var $modal = $form.parents('.modal');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function (error, results, $form) {
                if (error) {
                    return;
                }

                $('#org-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize()).done(function (response) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('admin.org.submit_success'));
                    refreshItem(response.parentId);
                }).fail(function () {
                    Notify.danger(Translator.trans('admin.org.submit_error'));
                });
            }
        });

        Validator.addRule('name_max', function (options) {
            let maxLength = true;
            let values = $(options.element).val().split('\n');
            values.map(function (value, index, array) {
                let l = calculateByteLength(value);
                if (l > 200) {
                    maxLength = false;
                }
            });
            return maxLength;
        }, Translator.trans('admin.org.name_max_message'));

        Validator.addRule('name_chinese_alphanumeric', function (options) {
            let alphanumericValidator = true;
            let values = $(options.element).val().split("\n");
            values.map(function (value, index, array) {
                alphanumericValidator = /^([\s]|[\u4E00-\uFA29]|[a-zA-Z0-9_.·])*$/i.test(value);
                if (alphanumericValidator == false) {
                    return alphanumericValidator;
                }
            });

            return alphanumericValidator;
        }, Translator.trans('admin.org.name_chinese_alphanumeric_message'));

        validator.addItem({
            element: '#org-names',
            required: true,
            rule: 'name_max name_chinese_alphanumeric remote'
        });

        function calculateByteLength(string) {
            let length = string.length;
            for (let i = 0; i < string.length; i++) {
                if (string.charCodeAt(i) > 127)
                    length++;
            }
            return length;
        }

        function refreshItem(orgId) {
            let $orgTreeItem = $('#tree-item-' + orgId);
            let $loadBtn = $("#load-btn-" + orgId);
            let isChildrenOpen = false;
            if ($loadBtn.length > 0 && $loadBtn.hasClass('glyphicon-chevron-down')) {
                isChildrenOpen = true;
            }
            $.get($orgTreeItem.data('refresh-url'), function (html) {
                if (!isChildrenOpen) {
                    $orgTreeItem.replaceWith(html);
                } else {
                    let contentWrapHtml = $orgTreeItem.find('.js-org-tree-item-wrap').html();
                    $orgTreeItem.replaceWith(html);
                    $newOrgTreeItem = $('#tree-item-' + orgId);
                    $newOrgTreeItem.find('.js-org-tree-item-wrap').html(contentWrapHtml);
                    refreshChildrenItems(orgId);
                }
            });
        }

        function refreshChildrenItems(parentId) {
            let $loadBtn = $("#load-btn-" + parentId);
            $loadBtn.toggleClass('glyphicon-chevron-right').toggleClass('glyphicon-chevron-down');
            let $orgTreeItem = $('#tree-item-' + parentId);
            let $contentWrap = $orgTreeItem.find('.js-org-tree-item-wrap');
            $.get($loadBtn.data('url'), function (html) {
                $contentWrap.html(html);
                $loadBtn.data('is-complete', true);
            });
        }

    };
});
