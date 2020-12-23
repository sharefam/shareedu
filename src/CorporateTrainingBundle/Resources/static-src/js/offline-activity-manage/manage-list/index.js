import notify  from 'common/notify';

class Page {
  constructor() {
    this.initOperate();
  }
  
  initOperate() {
    $('.offline-activity-operate').click(function () {

      let notifyTitle = $(this).data('notifyTitle');
      if (!confirm(Translator.trans('offline_activity.manage.make_sure', {'notifyTitle': notifyTitle}))) {
        return;
      }
      
      $.get($(this).data('url'), function(result){
        if (result) {
          notify('success', Translator.trans('offline_activity.manage.success_hint', {'notifyTitle': notifyTitle}));
          window.location.reload();
        } else {
          notify('danger', Translator.trans('offline_activity.manage.fail_hint', {'notifyTitle': notifyTitle}));
        }
      }).error(function() {
        notify('danger', Translator.trans('offline_activity.manage.fail_hint', {'notifyTitle': notifyTitle}));
      });
    });

    $('.publish-offline-activity').click(function () {

      let notifyTitle = $(this).data('notifyTitle');
      if (!confirm(Translator.trans('offline_activity.manage.make_sure', {'notifyTitle': notifyTitle}))) {
        return;
      }
      
      $.get($(this).data('url'), function(result){
        if (result.success) {
          notify('success', Translator.trans('offline_activity.manage.success_hint', {'notifyTitle': notifyTitle}));
          window.location.reload();
        } else {
          notify('danger', result.message);
        }
      }).error(function() {
        notify('danger', Translator.trans('offline_activity.manage.fail_hint', {'notifyTitle': notifyTitle}));
      });
    });
    
    $('.js-data-popover').popover({
      html: true,
      trigger: 'hover',
      placement: 'bottom',
      template: '<div class="popover tata-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
      content: function() {
        return $(this).siblings('.popover-content').html();
      }
    });
  }
}

new Page();
