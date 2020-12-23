import notify from 'common/notify';

export default class Review {
  constructor() {
    this.$form = $('#offline-exam-review-form');
    this.$modal = this.$form.parents('.modal');
    this.init();
  }

  init() {
    this.initValidator();
  }

  initValidator() {
    this.validator = this.$form.validate({
      rules: {
        score: {
          required: true,
          inputLessThan: true,
          arithmeticFloat: true,
          max: 500,
        },
      },
      messages: {
        score: {
          required: Translator.trans('project_plan.exam-manage.offline_exam'),
        },
      }
    });

    $('.js-save-btn').on('click', (e) => {
      if (this.validator.form()) {
        $('.js-save-btn').button('loading');
        $.post(this.$form.prop('action'), this.$form.serialize(), function (result) {
          if (result) {
            notify('success', Translator.trans('project_plan.exam-manage.review_success'));
            window.location.reload();
          } else {
            $('.js-save-btn').button('reset');
            notify('danger', Translator.trans('project_plan.save_error'));
          }
        }).error(function () {
          $('.js-save-btn').button('reset');
          notify('danger', Translator.trans('project_plan.save_error'));
        });
      }
    });
  }
}

new Review();

$.validator.addMethod('arithmeticFloat', function (value, element) {
  return this.optional(element) || /^[0-9]+(\.[0-9]?)?$/.test(value);
}, $.validator.format(Translator.trans('activity.testpaper_manage.arithmetic_float_error_hint')));

jQuery.validator.addMethod('inputLessThan', function() {
  let flag = true;
  let $inputScore = $('#score').val();
  let $max = $('#testPaper-score').text();
  if ($inputScore - $max > 0) {
    flag = false;
  }
  return flag;
}, '得分应该小于等于试卷满分');
