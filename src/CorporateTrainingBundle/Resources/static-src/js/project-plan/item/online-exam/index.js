import notify from 'common/notify';

export default class OnlineExam {
  constructor() {
    this.$form = $('#online-exam-form');
    this.$modal = this.$form.parents('.modal');
    this.init();
  }
  
  init() {
    this.initValidator();
    this.initDateTimePicker();
  }
  
  initValidator() {
    this.validator = this.$form.validate({
      rules: {
        name: {
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
        length: {
          required: true,
          max: 1000,
          digits: true,
        },
        passScore: {
          required: true,
          inputLessThan: true,
          arithmeticFloat: true,
          max: 500,
        },
        resitTimes: {
          required: true,
          max: 100,
          digits: true,
        },
        mediaId: {
          required: true,
        },
      },
      messages: {
        name: {
          required: Translator.trans('project_plan.exam-manage.title_required_message'),
        },
        startTime: {
          required: Translator.trans('project_plan.exam-manage.start_time.required_message'),
        },
        endTime: {
          required: Translator.trans('project_plan.exam-manage.end_time.required_message'),
        },
        length: {
          required: Translator.trans('project_plan.exam-manage.length_required_message'),
        },
        passScore: {
          required: Translator.trans('project_plan.exam-manage.pass_score.required_message'),
        },
        resitTimes: {
          required: Translator.trans('project_plan.exam-manage.resit_times_required_message'),
        },
        mediaId: {
          required: Translator.trans('project_plan.exam-manage.exam_paper_required_message'),
        }
      }
    });
  
    $('#create-form-submit').on('click', (e) => {
      if (this.validator.form()) {
        $('#create-form-submit').button('loading');
        $.post(this.$form.prop('action'), this.$form.serialize(),function (result) {
          if (result.success) {
            notify('success', Translator.trans('project_plan.save_success'));
            window.location.reload();
          } else {
            let message = result.message ? result.message : Translator.trans('project_plan.save_error');
            $('#create-form-submit').button('reset');
            notify('danger', message);
          }
        }).error(function() {
          $('#create-form-submit').button('reset');
          notify('danger', Translator.trans('project_plan.save_error'));
        });
      }
    });
  }
  
  initDateTimePicker() {
    $('#startTime, #endTime').datetimepicker({
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
  
}

new OnlineExam();

$.validator.addMethod('arithmeticFloat', function (value, element) {
  return this.optional(element) || /^[0-9]+(\.[0-9]?)?$/.test(value);
}, $.validator.format(Translator.trans('activity.testpaper_manage.arithmetic_float_error_hint')));

jQuery.validator.addMethod('inputLessThan', function() {
  let flag = true;
  let $inputScore = $('#passScore-input').val();
  let $max = $('#testPaper-score').text();
  if ($inputScore - $max > 0) {
    flag = false;
  }
  return flag;
}, Translator.trans('project_plan.exam-manage.input_less_than_message'));

$.validator.addMethod('endTime_input_check', function (value, element, params) {
  let inputStartTime = $('#startTime').val();
  let inputEndTime = $('#endTime').val();
  let endTime = new Date(inputEndTime.replace(/-/g, '/'));
  let startTime = new Date(inputStartTime.replace(/-/g, '/'));
  return this.optional(element) || endTime >= startTime;
},
Translator.trans('project_plan.exam-manage.endtime_input_check_message')
);
