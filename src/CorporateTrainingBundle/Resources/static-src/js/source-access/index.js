import CheckTreeviewInput from '../org-select/Org-CheckTreeview-input';
window.$.accessCheckTreeviewInput = CheckTreeviewInput;

class SourceAccess {
  constructor() {
    this.event();
    this.initTree();
    this.initDateTimePicker();
  }

  event() {
    $('.js-access-tab-link').click(function () {
      $(this).parents('.js-access-range').find('.active').removeClass('active');
      $(this).addClass('active');

      if($(this).hasClass('js-access-contitional-close')) {

        $('.js-access-setting').addClass('hidden');
        $('[name=conditionalAccess]').val(0);
      }else{
        $('.js-access-setting').removeClass('hidden');
        $('[name=conditionalAccess]').val(1);
      }
    });

    $('.js-hire-date').on('change', function (e) {
      if ($('#hireDateType').val() == 'between') {
        $('.js-day').addClass('hidden');
        $('.js-datetimepicker').addClass('hidden');
        $('.js-between-datetimepicker').removeClass('hidden');
      } else if ($('#hireDateType').val() == 'before' || $('#hireDateType').val() == 'after') {
        $('.js-day').addClass('hidden');
        $('.js-datetimepicker').removeClass('hidden');
        $('.js-between-datetimepicker').addClass('hidden');
      } else {
        $('.js-day').removeClass('hidden');
        $('.js-datetimepicker').addClass('hidden');
        $('.js-between-datetimepicker').addClass('hidden');
      }
    });
    $('#hireDateType').on('change', function (e) {
      if ($('#hireDateType').val() != 'lessThanOrEqual' || 'greatThanOrEqual') {
        $('[name=days]').val(null);
      }
    });


    $('.es-switch').on('click' , function (e) {
      let $input = $(this).find('.es-switch__input');
      let ToggleVal = $input.val() == $input.data('open') ? $input.data('close') : $input.data('open');
      $input.val(ToggleVal);
      $(this).toggleClass('is-active');
      if ($input.val() == 1) {
        $('.js-access-setting').removeClass('hidden');
      } else {
        $('.js-access-setting').addClass('hidden');
      }
    });
  }

  initDateTimePicker() {
    $('[name=hireStartDate]').datetimepicker({
      autoclose: true,
      language: document.documentElement.lang,
      format: 'yyyy-mm-dd',
      startView: 2,
      minView:'month'
    }).on('changeDate', () => {
      $('[name=hireEndDate]').datetimepicker('setStartDate', $('[name=hireStartDate]').val().substring(0, 16));
    });

    $('[name=hireEndDate]').datetimepicker({
      autoclose: true,
      language: document.documentElement.lang,
      format: 'yyyy-mm-dd',
      startDate:  new Date(Date.now()+5*60),
      minView:'month'
    }).on('changeDate', () => {
      $('[name=hireStartDate]').datetimepicker('setEndDate', $('[name=hireEndDate]').val().substring(0, 16));
    });

    $('[name=date]').datetimepicker({
      autoclose: true,
      language: document.documentElement.lang,
      format: 'yyyy-mm-dd',
      startView: 2,
      minView:'month'
    }).on('changeDate', () => {
      $('[name=date]').datetimepicker('setStartDate', $('[name=date]').val().substring(0, 16));
    });
  }

  initTree() {
    if ($('#accessOrgsSelect').length) {
      new window.$.accessCheckTreeviewInput({
        $elem: $('#accessOrgsSelect'),
        disableNodeCheck: false,
        saveColumn: 'id',
        transportParent: true,
      });
    }

    if ($('#accessPostsSelect').length) {
      new window.$.accessCheckTreeviewInput({
        $elem: $('#accessPostsSelect'),
        disableNodeCheck: false,
        saveColumn: 'id',
        transportChildren: true,
      });
    }

    if ($('#accessUserGroupsSelect').length) {
      new window.$.accessCheckTreeviewInput({
        $elem: $('#accessUserGroupsSelect'),
        disableNodeCheck: false,
        saveColumn: 'id',
        transportChildren: true,
      });
    }

    if ($('#accessPostRanksSelect').length) {
      new window.$.accessCheckTreeviewInput({
        $elem: $('#accessPostRanksSelect'),
        disableNodeCheck: false,
        saveColumn: 'id',
        transportChildren: true,
      });
    }
  }
}

new SourceAccess();
