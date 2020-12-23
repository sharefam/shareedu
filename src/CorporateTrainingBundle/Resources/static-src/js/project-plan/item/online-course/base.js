import notify from 'common/notify';

export default class Base {
  constructor() {
    this.$form = $('#project-plan-item-form');
    this.$modal = this.$form.parents('.modal');

    this.$attachmentModal = $('#attachment-modal', window.parent.document);
    this.$form.on('click', '[data-role="select-offline-course"]', event => this.showSelectCourse(event));
    this.$form.on('click', '.js-close-icon', event => this.removeItem(event));
    this.$form.on('click', '#assignment-base-submit', event => this.submitCourse(event));
    this.validator = this.$form.validate({
      rules: {
        startTime: {
          required: true,
        },
        endTime: {
          required: true,
          endTime_input_check: true,
        },
      },
      messages: {
        startTime: {
          required: Translator.trans('project_plan.online_course.start_time_required_message'),
        },
        endTime: {
          required: Translator.trans('project_plan.online_course.end_time_required_message'),
        },
      }
    });
    this.initDateTimePicker();
  }

  submitCourse(event) {
    if (this.$form.find('.project-plan-sortable-item').length < 1) {
      notify('danger', Translator.trans('project_plan.online_course.add_empty'));
      return;
    }
    if (this.validator.form()) {
      let selectedIds = [];
      $('.project-plan-sortable-item',window.parent.document).each(function (e,data) {
        selectedIds.push($(this).data('id'));
      });
      let self = this;
      $.post(this.$form.attr('action'), this.$form.serialize()+ '&courseIds=' + JSON.stringify(selectedIds), function (result) {
        if (result) {
          self.$modal.modal('hide');
          notify('success',  Translator.trans('project_plan.save_success'));
          window.location.reload();
        } else {
          $('#assignment-base-submit').button('reset');
          notify('danger',  Translator.trans('project_plan.save_error'));
        }
      }).error(function() {
        $('#assignment-base-submit').button('reset');
        notify('danger',  Translator.trans('project_plan.save_error'));
      });
    }

  }

  showSelectCourse(event) {
    event.preventDefault();
    let $btn = $(event.currentTarget);

    this.$attachmentModal.modal().data('manager', this);
    $.get($btn.data('url'),html => {
      this.$attachmentModal.html(html);
    });

  }

  removeItem(event) {
    event.preventDefault();
    let $item = $(event.currentTarget);
    $item.parents('.project-plan-sortable-item').remove();

  }

  initDateTimePicker() {
    $('[name=startTime]').datetimepicker({
      autoclose: true,
      language: document.documentElement.lang,
      format: 'yyyy-mm-dd hh:ii',
      startView: 2
    }).on('changeDate', () => {
      $('[name=endTime]').datetimepicker('setStartDate', $('[name=startTime]').val().substring(0, 16));
    });

    $('[name=endTime]').datetimepicker({
      autoclose: true,
      language: document.documentElement.lang,
      format: 'yyyy-mm-dd hh:ii',
      startDate:  new Date(Date.now()+5*60)
    }).on('changeDate', () => {
      $('[name=startTime]').datetimepicker('setEndDate', $('[name=endTime]').val().substring(0, 16));
    });
  }
}

$.validator.addMethod('endTime_input_check', function (value, element, params) {
  let inputStartTime = $('#startTime').val();
  let inputEndTime = $('#endTime').val();
  let endTime = new Date(inputEndTime.replace(/-/g, '/'));
  let startTime = new Date(inputStartTime.replace(/-/g, '/'));
  return this.optional(element) || endTime >= startTime;
},
Translator.trans('project_plan.exam-manage.endtime_input_check_message')
);
