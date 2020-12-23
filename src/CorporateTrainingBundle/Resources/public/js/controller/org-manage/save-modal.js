define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    var $form = $('#org-manage-form');
    var $modal = $form.parents('.modal');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return;
        }
        $('#org-create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function (response) {
          $modal.modal('hide');
          Notify.success(Translator.trans('保存组织机构成功！'));
          refreshItem(response.orgId);
        }).fail(function () {
          Notify.danger(Translator.trans('添加组织机构失败，请重试！'));
        });
      }
    });

    validator.addItem({
      element: '#org-name-field',
      required: true,
      rule: 'chinese_alphanumeric byte_maxlength{max:200} remote'
    });

    validator.addItem({
      element: '#org-code-field',
      required: true,
      rule: 'alpha_numerica byte_maxlength{max:30} remote'
    });


    Validator.addRule("alpha_numerica", /^([0-9]|[a-zA-Z])*$/i, `{{display}}${Translator.trans('admin.user_group.alpha_numerica_message')}`);

    $modal.find('.delete-org').on('click', function () {
      if (confirm(Translator.trans('admin.org.delete_confirm_message'))) {
        $.post($(this).data('url'), function (response) {
          if (response && response.status == 'error') {
            var msg = Translator.trans('admin.org.delete_error_one');
            $.each(response.data, function ($key) {
              msg += Translator.trans($key) + ':' + response.data[$key] + Translator.trans('admin.org.delete_count') + " \t";
            });
            msg += Translator.trans('admin.org.delete_error_two');
            Notify.danger(msg, 8);
            return false;
          }
          Notify.success(Translator.trans('admin.org.delete_success'));
          $modal.modal('hide');
          updateParentItemChildrenNum(response.orgId);
        });
      }
    });

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
                let $newLoadBtn = $("#load-btn-" + orgId);
                $newLoadBtn.toggleClass('glyphicon-chevron-right').toggleClass('glyphicon-chevron-down');
                $newLoadBtn.data('is-complete', true);
            }
        });
    }

    function updateParentItemChildrenNum(orgId) {
        let $item = $('#tree-item-' + orgId);
        let $parentItem = $item.parent().closest('.js-org-tree-item');
        let $parentItemChildren = $parentItem.children('.org-tree-item__head').find('div.org-tree-item__children_num');
        let parentItemChildrenNum = $parentItemChildren.text()
        $parentItemChildren.text(parentItemChildrenNum-1);
        $item.remove();
    }
  }

});
