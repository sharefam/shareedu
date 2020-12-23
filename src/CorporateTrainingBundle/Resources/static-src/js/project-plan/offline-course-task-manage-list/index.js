import notify  from 'common/notify';
import Sortable from 'common/sortable';

new Sortable({
  element: '#sortable-list',
  ajax: false
}, function(data) {
  $.post($('#sortable-list').data('sorturl'), { ids: data }, (response) => {

  });

  $('#sortable-list').find('.drag').each(function(index, item) {
    $(item).find('.js-project-unit-number').text(`${index + 1}.`);
  });
});

$('.remove-item').click(function(){
  
  if (!confirm(Translator.trans('project_plan.item.remove_confirm_message'))) {
    return;
  }
  
  let courseId = $(this).data('courseid');
  let taskId = $(this).data('taskid');
  
  $.post($(this).data('url'), function(result){
    if (result) {
      $('#course-' + courseId + '-task-' + taskId).remove();
      notify('success', Translator.trans('project_plan.item.remove_success_message'));
      window.location.reload();
    } else {
      notify('success', Translator.trans('project_plan.item.remove_error_message'));
    }
  }).error(function() {
    notify('success', Translator.trans('project_plan.item.remove_error_message'));
  });
});
