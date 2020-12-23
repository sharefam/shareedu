import notify  from 'common/notify';

class Page {
  constructor() {
    this.init();
  }

  init() {
    this.initTreeviewInput();
    this.initEvent();
    this.initPaginationEvent();
  }

  initEvent() {
    var $table = $('.data-list');

    $table.on('click', '.close-project-plan', function () {
      if (!confirm(Translator.trans('project_plan.close_confirm_message'))) return false;
      $.post($(this).data('url'), function () {
        notify('success', Translator.trans('project_plan.close_success_message'));
        window.location.reload();
      });
    });

    $table.on('click', '.delete-project-plan', function () {
      if (!confirm(Translator.trans('project_plan.delete_confirm_message'))) return false;
      $.post($(this).data('url'), function () {
        notify('success',Translator.trans('project_plan.delete_success_message'));
        window.location.reload();
      });
    });

    $table.on('click', '.archive-project-plan', function () {
      if (!confirm(Translator.trans('project_plan.archive_confirm_message'))) return false;
      $.post($(this).data('url'), function () {
        notify('success',Translator.trans('project_plan.archive_success_message'));
        window.location.reload();
      });
    });

    $table.on('click', '.cancel-archive-project-plan', function () {
      if (!confirm(Translator.trans('project_plan.cancel_archive_confirm_message'))) return false;
      $.post($(this).data('url'), function () {
        notify('success',Translator.trans('project_plan.cancel_archive_success_message'));
        window.location.reload();
      });
    });

    $table.on('click', '.publish-project-plan', function () {
      if (!confirm(Translator.trans('project_plan.publish_confirm_message'))) return false;
      $.post($(this).data('url'), function (response) {
        if (!response['success']) {
          notify('danger',response['message']);
        } else {
          notify('success',Translator.trans('project_plan.publish_success_message'));
          window.location.reload();
        }
      }).error(function (e) {
        var res = e.responseJSON.error.message || '未知错误';
        notify('danger',res);
      });
    });
  }

  initPaginationEvent() {
    $('.data-list').on('click', '.pagination li', function () {
      var url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('#project-plan-search-form').serialize(), function (data) {
          $('.data-list').html(data);
          $('[data-toggle="popover"]').popover();
        });
      }
    });
  }

  initTreeviewInput() {
    new window.$.CheckTreeviewInput({
      $elem: $('#resource-orgCode'),
      saveColumn: 'id',
      disableNodeCheck: false,
      transportChildren: true,
    });
  }
}

new Page();

