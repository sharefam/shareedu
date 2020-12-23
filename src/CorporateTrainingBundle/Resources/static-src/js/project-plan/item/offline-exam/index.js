import ReactDOM from 'react-dom';
import React from 'react';
import PersonaMultiInput from 'app/common/component/persona-multi-input';
import notify from 'common/notify';

export default class Create {
  constructor() {
    this.$element = $('#offline-exam-form');
    this.$modal = this.$element.parents('.modal');
    this.init();
  }
  
  init() {
    this.initValidator();
    this.initDateTimePicker();
  }
  
  initValidator() {
    this.validator = this.$element.validate({
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
        score: {
          required: true,
          positive_integer: true,
        },
        passScore: {
          required: true,
          inputLessThan: true,
          positive_integer: true,
          max: 500,
        }
      },
      messages: {
        title: {
          required: Translator.trans('project_plan.title_empty'),
          trim: Translator.trans('project_plan.title_empty'),
        },
        startTime: {
          required: Translator.trans('project_plan.exam-manage.start_time.required_message')
        },
        endTime: {
          required: Translator.trans('project_plan.exam-manage.end_time.required_message')
        },
        place: {
          required: Translator.trans('project_plan.exam-manage.place.required_message')
        },
        score: {
          required: Translator.trans('project_plan.exam-manage.score.required_message')
        },
        passScore: {
          required: Translator.trans('project_plan.exam-manage.pass_score.required_message')
        },
      }
    });
  
    $('#create-offline-exam-btn').click(event => {
      if (this.validator.form()) {
        $('#create-offline-exam-btn').button('loading');
        $.post(this.$element.prop('action'), this.$element.serialize(),function (result) {
          if (result) {
            notify('success', Translator.trans('project_plan.save_success'));
            window.location.reload();
          } else {
            $('#create-offline-exam-btn').button('reset');
            notify('danger', Translator.trans('project_plan.save_error'));
          }
        }).error(function() {
          $('#create-offline-exam-btn').button('reset');
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

new Create();

$.validator.addMethod('arithmeticFloat', function (value, element) {
  return this.optional(element) || /^[0-9]+(\.[0-9]?)?$/.test(value);
}, $.validator.format(Translator.trans('activity.testpaper_manage.arithmetic_float_error_hint')));

jQuery.validator.addMethod('inputLessThan', function() {
  let flag = true;
  let $inputScore = $('#passScore').val();
  let $max = $('#score').val();
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
