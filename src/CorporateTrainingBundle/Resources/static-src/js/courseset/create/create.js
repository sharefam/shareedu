export default class Create {
  constructor($element) {
    this.$element = $element;
    this.$courseSetType = this.$element.find('.js-courseSetType');
    this.$currentCourseSetType = this.$element.find('.js-courseSetType.active');
    this.init();
    this.initExpiryMode();
    this.checkBoxChange();
  }

  init() {
    this.validator = this.$element.validate({
      currentDom: '#courseset-create-btn',
      rules: {
        title: {
          byte_maxlength: 200,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          course_title: true
        },
        orgCode: {
          required: true
        }
      },
      messages: {
        title: {
          required: Translator.trans('course_set.title_required_error_hint'),
          trim: Translator.trans('course_set.title_required_error_hint'),
        }
      }
    });
    this.initDatePicker('#expiryStartDate');
    this.initDatePicker('#expiryEndDate');
    this.initDatePicker('#deadline');

    this.$courseSetType.click(event => {
      this.$courseSetType.removeClass('active');
      this.$currentCourseSetType = $(event.currentTarget).addClass('active');
      $('input[name="type"]').val(this.$currentCourseSetType.data('type'));
      let $title = $('#course_title');
      $title.rules('remove');
      if (this.$currentCourseSetType.data('type') != 'live') {
        $title.rules('add', {
          required: true,
          trim: true,
          course_title: true,
        });
        $('.js-learn-mod').show();
      } else {
        $title.rules('add', {
          required: true,
          trim: true,
          open_live_course_title: true,
        });
        $('.js-learn-mod').hide();
        $('[value="freeMode"]').click();
      }

    });

    $('#courseset-create-btn').click(event => {
      if (this.validator.form()) {
        this.$element.submit();
      }
    });
  }
  initDatePicker($id) {
    let $picker = $($id);
    $picker.datetimepicker({
      format: 'yyyy-mm-dd',
      language: 'zh',
      minView: 2, //month
      autoclose: true,
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    }).on('hide', () => {
      this.validator.form();
    });
    $picker.datetimepicker('setStartDate', new Date());
  }
  initExpiryMode() {
    let $deadline = $('[name="deadline"]');
    let $expiryDays = $('[name="expiryDays"]');
    let $expiryStartDate = $('[name="expiryStartDate"]');
    let $expiryEndDate = $('[name="expiryEndDate"]');
    let expiryMode = $('[name="expiryMode"]:checked').val();

    this.elementRemoveRules($deadline);
    this.elementRemoveRules($expiryDays);
    this.elementRemoveRules($expiryStartDate);
    this.elementRemoveRules($expiryEndDate);

    switch (expiryMode) {
    case 'days': {
      let $deadlineType = $('[name="deadlineType"]:checked');
      if ($deadlineType.val() === 'end_date') {
        this.elementAddRules($deadline, this.getDeadlineEndDateRules());
        this.validator.form();
        return;
      }
      this.elementAddRules($expiryDays, this.getExpiryDaysRules());
      this.validator.form();
      break;
    }
    case 'date': {
      this.elementAddRules($expiryStartDate, this.getExpiryStartDateRules());
      this.elementAddRules($expiryEndDate, this.getExpiryEndDateRules());
      this.validator.form();
      break;
    }
    default:
      break;
    }
  }

  checkBoxChange() {
    $('input[name="deadlineType"]').on('change', (event) => {
      if ($('input[name="deadlineType"]:checked').val() == 'end_date') {
        $('#deadlineType-date').removeClass('hidden');
        $('#deadlineType-days').addClass('hidden');
      } else {
        $('#deadlineType-date').addClass('hidden');
        $('#deadlineType-days').removeClass('hidden');
      }
      this.initExpiryMode();
    });

    $('input[name="expiryMode"]').on('change', (event) => {
      if ($('input[name="expiryMode"]:checked').val() == 'date') {
        $('#expiry-days').removeClass('hidden').addClass('hidden');
        $('#expiry-date').removeClass('hidden');
      } else if ($('input[name="expiryMode"]:checked').val() == 'days') {
        $('#expiry-date').removeClass('hidden').addClass('hidden');
        $('#expiry-days').removeClass('hidden');
        $('input[name="deadlineType"][value="days"]').prop('checked', true);
      } else {
        $('#expiry-date').removeClass('hidden').addClass('hidden');
        $('#expiry-days').removeClass('hidden').addClass('hidden');
      }
      this.initExpiryMode();
    });

    $('input[name="learnMode"]').on('change', (event) => {
      if ($('input[name="learnMode"]:checked').val() == 'freeMode') {
        $('#learnLockModeHelp').removeClass('hidden').addClass('hidden');
        $('#learnFreeModeHelp').removeClass('hidden');
      } else {
        $('#learnFreeModeHelp').removeClass('hidden').addClass('hidden');
        $('#learnLockModeHelp').removeClass('hidden');
      }
    });
  }

  getExpiryEndDateRules() {
    return {
      required: true,
      date: true,
      after_date: '#expiryStartDate',
      messages: {
        required:Translator.trans('course.manage.expiry_end_date_error_hint')
      }
    };
  }

  getExpiryStartDateRules() {
    return {
      required: true,
      date: true,
      after_now_date: true,
      before_date: '#expiryEndDate',
      messages: {
        required: Translator.trans('course.manage.expiry_start_date_error_hint')
      }
    };
  }

  getExpiryDaysRules() {
    return {
      required: true,
      positive_integer: true,
      max_year: true,
      messages: {
        required: Translator.trans('course.manage.expiry_days_error_hint')
      }
    };
  }

  getDeadlineEndDateRules() {
    return {
      required: true,
      date: true,
      after_now_date: true,
      messages: {
        required: Translator.trans('course.manage.deadline_end_date_error_hint')
      }
    };
  }
  elementAddRules($element, options) {
    $element.rules('add', options);
  }

  elementRemoveRules($element) {
    $element.rules('remove');
  }
}
