import notify from 'common/notify';
import CheckTreeviewInput from '../../../../../../common/CheckTreeview-input';
import BatchSelect from 'app/common/widget/batch-select';

class list
{
  constructor() {
    this.$container = $('#project-plan-offline-course-attendance-container');
    this.$taskChoose = $('.js-task-choose');
    this.init();
    this.checkBatchUpdate();
  }

  init() {
    this.initOrgTreeInput();
    this.initTaskChoose();
    this.initBatchTaskSelect();
    this.initEvent();
  }

  initEvent() {
    $('.data-list').on('click', '.pagination li', function () {
      let url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('.department-manage-search-form').serialize(), function (data) {
          $('.data-list').html(data);
          $('[data-toggle="popover"]').popover();
        });
      }
    });

    $('.js-exporter').on('click', function () {
      let exportForm = $('.form-inline');
      let url = $(this).data('url');
      $(exportForm).attr('action', url);
      exportForm.submit();
      $(exportForm).attr('action', '');
    });
  }

  initOrgTreeInput() {
    (function initTreeviewInput() {
      new window.$.CheckTreeviewInput({
        $elem: $('#resource-orgCode'),
        saveColumn: 'id',
        disableNodeCheck: false,
        transportChildren: true,
      });
    })();
  }

  initTaskChoose() {
    this.$taskChoose.select2();

    this.$taskChoose.on('change', function () {
      let $url = $('.js-task-choose option:selected').attr('href');
      location.href = $url;
    });
  }

  checkBatchUpdate() {
    $('#js-update-attendance-btn').on('click', function(e) {
      if (getCheckstatus(true)) {
        notify('warning', Translator.trans('project_plan.verify_list_user_empty_message'));
        e.stopImmediatePropagation();
      }
    });

    let getCheckstatus = function(ischeck) {
      let status = true;
      $('[data-role=\'batch-item\']').each(function() {
        if ($(this).prop('checked') === ischeck) {
          status = false;
          return;
        }
      });
      return status;
    };
  }

  initBatchTaskSelect() {
    new BatchSelect(this.$container);
  }
}

new list();
