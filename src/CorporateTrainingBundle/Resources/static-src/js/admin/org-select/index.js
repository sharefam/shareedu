import notify  from 'common/notify';

class List {
  constructor() {
    this.init();
  }

  init() {
    this.initTreeviewInput();
    this.initPaginationEvent();
    this.initBathUpdateOrg();
  }

  initPaginationEvent() {
    $('.data-list').on('click', '.pagination li', function () {
      var url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('.js-data-search').serialize(), function (data) {
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

  initBathUpdateOrg(){
    if($('#batch-update-org').length > 0){
      var initDom = function() {
        var formId = $('.data-list').find('#batch-update-org').data('formId');
        var generate = $('.data-list').find('#batch-update-org').data('generate');
        if(generate == false){
          return;
        }
      };

      var getCheckstatus = function(ischeck) {
        var status = true;
        $('.data-list').find('[data-role=batch-select]').parents('table').find('[data-role=batch-item]').each(function() {
          if ($(this).prop('checked') === ischeck) {
            status = false;
            return;
          }
        });
        return status;
      };

      initDom();

      $('.data-list').on('click','[data-role=batch-select]', function() {
        console.log($(this).prop('checked'));
        if ($(this).prop('checked')) {
          $('.data-list').find('[data-role=batch-select]').parents('table').find('[data-role=batch-item]').prop('checked', true);
          $('.data-list').find('[data-role=batch-select]').prop('checked', true);
        } else {
          $('.data-list').find('[data-role=batch-select]').parents('table').find('[data-role=batch-item]').prop('checked', false);
          $('.data-list').find('[data-role=batch-select]').prop('checked', false);
        }
      });

      $('.data-list').on('click', '[data-role=batch-item]', function() {
        $('.data-list').find('[data-role=batch-select]').prop('checked', getCheckstatus(false));
      });

      $('.data-list').on('click','#batch-update-org', function(e) {
        if (getCheckstatus(true)) {
          notify('warning',Translator.trans('admin.org.selected_empty'));
          e.stopImmediatePropagation();
        }
      });
    }
  }
}

new List();
