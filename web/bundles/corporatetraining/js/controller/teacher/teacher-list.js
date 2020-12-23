define(function (require, exports, module) {

  let Notify = require('common/bootstrap-notify');
  let OverviewDateRangePicker = require('./date-range-picker');
  exports.run = function () {
    let $table = $('#teacher-table');
    this.dateRangePicker = new OverviewDateRangePicker();
    $table.on('click', '.cancel-promote-teacher', function () {
      if (!confirm(Translator.trans('admin.teacher.list.cancel_promote_confirm_message'))) {
        return;
      }

      let $tr = $(this).parents('tr');
      $.post($(this).data('url'), function (html) {
        Notify.success(Translator.trans('admin.teacher.list.cancel_promote_success'));
        $tr.find('.promoted').addClass('hidden');
        let $newTr = $(html);
        $tr.find('.dropdown-menu').replaceWith($newTr.find('.dropdown-menu'));
      });

    });

    $table.on('click', '.promote-teacher', function () {
      if (!confirm(Translator.trans('admin.teacher.list.promote_confirm_message'))) {
        return;
      }
      let $tr = $(this).parents('tr');
      $.post($(this).data('url'), function (html) {
        Notify.success(Translator.trans('admin.teacher.list.promote_success'));
        $tr.find('.promoted').removeClass('hidden');
        let $newTr = $(html);
        $tr.find('.dropdown-menu').replaceWith($newTr.find('.dropdown-menu'));
        console.log($newTr.find('.dropdown-menu'));
      });

    });

    let $element = $('#teacher-search-container');

    let getCheckstatus = function (ischeck) {
      let status = true;
      $('[data-role=batch-select]').parents('table').find('[data-role=batch-item]').each(function () {
        if ($(this).prop('checked') === ischeck) {
          status = false;
          return;
        }
      });
      return status;
    };


    $('[data-role=batch-select]').on('click', function () {
      if ($(this).prop('checked')) {
        $('[data-role=batch-select]').parents('table').find('[data-role=batch-item]').prop('checked', true);
        $('[data-role=batch-select]').prop('checked', true);
      } else {
        $('[data-role=batch-select]').parents('table').find('[data-role=batch-item]').prop('checked', false);
        $('[data-role=batch-select]').prop('checked', false);
      }
    });

    $('[data-role=batch-select]').parents('table').on('click', '[data-role= batch-item]', function () {
      $('[data-role=batch-select]').prop('checked', getCheckstatus(false));
    });

    $('#batch-set-profile').on('click', function (e) {
      if (getCheckstatus(true)) {
        Notify.warning(Translator.trans('admin.teacher.list.set_profile_empty'));
        e.stopImmediatePropagation();
      }
    });

    $('.js-tooltip-twig-widget').find('.js-twig-widget-tips').each(function () {
      let $self = $(this);
      $self.popover({
        html: true,
        trigger: 'hover',//'hover','click'
        placement: $self.data('placement'),//'bottom',
        content: $self.next('.js-twig-widget-html').html()
      });
    });

    new window.$.CheckTreeviewInput({
      $elem: $('#teacher-manage-orgCode'),
      selectType: 'single',
    });
  };
});
