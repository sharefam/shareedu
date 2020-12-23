import CheckTreeviewInput from '../../common/CheckTreeview-input';
import BatchSelectInCookie from 'app/common/widget/batch-select-in-cookie';
import notify  from 'common/notify';

let batchSelectInCookie = new BatchSelectInCookie($('#project-plan-member-container'), 'projectPlanMemberIds');

class List {
  constructor() {
    this.initTreeViewInput();
    this.initSelect();
    this.initBatchDelete();
    this.initAssignmentOperate();
    this.initSearchCondition();
  }
  initSelect() {
    $('[data-role="tree-select"], [name="categoryId"]').select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first',
      allowClear: true,
    });
  }
  initTreeViewInput() {
    let $treeview = $('.js-treeview-select-wrap');
    for (let i = 0; i < $treeview.length; i++) {
      new CheckTreeviewInput({$elem: $treeview.eq(i)});
    }
  }
  initBatchDelete() {
    var $element = $('#project-plan-member-container');
    $element.on('click', '[data-role="batch-delete"]', function () {
      var $btn = $(this);
      var name = $btn.data('name');
      var ids = batchSelectInCookie.itemIds;
      console.log(ids.length);
      if (ids.length == 0) {
        notify('danger', Translator.trans('project_plan.item.member.selsect_empty') + name);
        return;
      }
      if (!confirm(Translator.trans('project_plan.item.member.batch_delete_confirm_message', {ids: ids.length, name: name}))) {
        return;
      }
      $element.find('.btn').addClass('disabled');
      notify('info', Translator.trans('project_plan.item.member.batch_delete_wait', {name: name}), 60);
      $.post($btn.data('url'), {ids: ids}, function () {
        batchSelectInCookie._clearCookie();
        window.location.reload();
        batchSelectInCookie._clearCookie();
      });
    });
  }
  initAssignmentOperate() {
    $('.projectplan-operate').click(function () {
      let notifyTitle = $(this).data('notifyTitle');
      if (!confirm(`确定要${notifyTitle}`)) {
        return;
      }
      $.get($(this).data('url'), function (result) {
        if (result) {
          notify('success', `${notifyTitle}成功!`);
          window.location.reload();
        } else {
          notify('danger', `${notifyTitle}失败!`);
        }
      }).error(function () {
        notify('danger', `${notifyTitle}失败!`);
      });
    });
  }
  initSearchCondition() {
    let $element = '#member-status';
    let value = $($element).find('option:selected').val();
    if (value == 'finished') {
      $('#choose-delay').removeClass('hidden');
    }
    $($element).change(function () {
      let value = $($element).find('option:selected').val();
      if (value == 'finished') {
        $('#choose-delay').removeClass('hidden');
      } else {
        $('#choose-delay').addClass('hidden');
      }
    });
  }
}
new List();
