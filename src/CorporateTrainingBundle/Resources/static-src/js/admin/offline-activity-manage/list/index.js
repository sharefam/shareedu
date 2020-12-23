import notify  from 'common/notify';

class Page {
  constructor() {
    this.init();
  }

  init() {
    this.initOperate();
    this.initTreeviewInput();
    this.initEvent();
    this.initPaginationEvent();
  }

  initEvent() {
    $('.js-data-popover').popover({
      html: true,
      trigger: 'hover',
      placement: 'bottom',
      template: '<div class="popover tata-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
      content: function() {
        var html = $(this).siblings('.popover-content').html();

        return html;
      }
    });
  }

  initOperate() {

    var $table = $('.data-list');

    $table.on('click', '.close-offline-activity', function () {
      if (!confirm(Translator.trans('admin.offline_activity.close_sure_hint'))) return false;
      $.post($(this).data('url'), function (html) {
        var $tr = $(html);
        $table.find('#' + $tr.attr('id')).replaceWith(html);
        notify('success',Translator.trans('admin.offline_activity.close_success_hint'));
      });
    });

    $table.on('click', '.publish-offline-activity', function() {
      if (!confirm(Translator.trans('admin.offline_activity.publish_sure_hint'))) return false;
      $.post($(this).data('url'), function(response) {
        if (!response['success']) {
          notify('danger',response['message']);
        } else {
          var $tr = $(response.message);
          $table.find('#' + $tr.attr('id')).replaceWith(response.message);
          notify('success', Translator.trans('admin.offline_activity.publish_success_hint'));
        }
      }).error(function(e) {
        var res = e.responseJSON.error.message || Translator.trans('admin.offline_activity.error.undefined_error_hint');
        notify('danger', res);
      });
    });
  }

  initPaginationEvent() {
    $('.data-list').on('click', '.pagination li', function () {
      var url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('.department-manage-search-form').serialize(), function (data) {
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
