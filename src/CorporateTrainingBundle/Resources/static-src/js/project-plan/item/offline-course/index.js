import ReactDOM from 'react-dom';
import React from 'react';
import PersonaMultiInput from 'app/common/component/persona-multi-input';
import notify from 'common/notify';

export default class Create {
  constructor() {
    this.$element = $('#offline-course-form');
    this.$modal = this.$element.parents('.modal');
    this.init();
  }
  
  init() {
    this.validator = this.$element.validate({
      currentDom: '#courseset-create-btn',
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
        }
      },
      messages: {
        title: {
          required: Translator.trans('project_plan.secondary_confirmation'),
          trim: Translator.trans('project_plan.secondary_confirmation'),
        }
      }
    });
    
    ReactDOM.render(
      <PersonaMultiInput
        addable={true}
        dataSource={$('#course-teachers').data('init-value')}
        outputDataElement='teachers'
        inputName="ids[]"
        searchable={{enable: true, url: $('#course-teachers').data('query-url') + '?q='}}
      />,
      document.getElementById('course-teachers')
    );
    
    $('#offline-course-btn').click(event => {
      if (this.validator.form()) {
        if($('input[name=teachers]').val() !== '[]' && $('.multi-list li').length == 1) {
          $('#offline-course-btn').button('loading');
          $.post(this.$element.prop('action'), this.$element.serialize(),function (result) {
            if (result) {
              notify('success', Translator.trans('project_plan.save_success'));
              window.location.reload();
            } else {
              $('#offline-course-btn').button('reset');
              notify('danger', Translator.trans('project_plan.save_error'));
            }
          }).error(function() {
            $('#offline-course-btn').button('reset');
            notify('danger', Translator.trans('project_plan.save_error'));
          });
        }else{
          notify('warning', Translator.trans('project_plan.offline_course.teacher.set'));
        }
      }
    });
  }
}

new Create();
