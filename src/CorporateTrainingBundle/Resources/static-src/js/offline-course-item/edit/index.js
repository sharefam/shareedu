import notify from 'common/notify';

export default class Edit {
  constructor() {
    this.$form = $('#offline-course-task-create-form');
    this.$modal = this.$form.parents('.modal');
    this.init();
  }
  
  init() {
    this.initValidator();
    this.initDateTimePicker();
    // this.initHomeWorkDom();
  }
  
  initValidator() {
    this.validator = this.$form.validate({
      rules: {
        title: {
          maxlength: 60,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          course_title: true
        },
        startTime: {
          required: true,
        },
        endTime: {
          required: true,
          endTime_input_check: true,
        },
        place: {
          required: true,
        },
        homeworkDeadline: {
          required: {
            depends() {
              return $('#hasHomework').is(':checked');
            }
          },
        },
        homeworkDemand: {
          required: {
            depends() {
              return $('#hasHomework').is(':checked');
            }
          },
        }
      },
      messages: {
        startTime: {
          required: Translator.trans('project_plan.base.start_time.required_message'),
        },
        endTime: {
          required: Translator.trans('project_plan.base.end_time.required_message'),
        },
      }
    });
    
    $('#create-form-submit').on('click', (e) => {
      if (this.validator.form()) {
        $('#create-form-submit').button('loading');
        this.$form.submit();
      }
    });
    $('[data-toggle=\'popover\']').popover();
  }
  
  initDateTimePicker() {
    $('#startTime, #endTime, #homeworkDeadline').datetimepicker({
      format: 'yyyy-mm-dd hh:ii',
      language: document.documentElement.lang,
      minView: 0,
      autoclose: true,
      startView: 2
    });
  
    $('#startTime').on('changeDate',function(){
      $('#endTime').datetimepicker('setStartDate',$('#startTime').val().substring(0,16));
    });
  
    $('#endTime').on('changeDate',function(){
      $('#startTime').datetimepicker('setEndDate',$('#endTime').val().substring(0,16));
    });
  }
  
  initHomeWorkDom() {
    $('#hasHomework').on('click', (e) => {
      if ($(e.target).is(':checked')) {
        $('.homework-require').removeClass('hidden');
      } else {
        $('.homework-require').addClass('hidden');
      }
    });
  }
}

$.validator.addMethod('endTime_input_check', function (value, element, params) {
  let inputStartTime = $('#startTime').val();
  let inputEndTime = $('#endTime').val();
  let endTime = new Date(inputEndTime.replace(/-/g, '/'));
  let startTime = new Date(inputStartTime.replace(/-/g, '/'));
  return this.optional(element) || endTime >= startTime;
}, Translator.trans('project_plan.base.enrollment_end_date_check')
);

new Edit();
