import ModalController from '../../common/modal-controller';
import notify  from 'common/notify';
import Sortable from 'common/sortable';


new ModalController();

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

$('[id=remove-item]').click(function () {

  if (!confirm(Translator.trans('project_plan.item.remove_confirm_message'))) {
    return;
  }

  $.post($(this).data('url'), function(result){
    if (result) {
      notify('success', Translator.trans('project_plan.item.remove_success_message'));
      window.location.reload();
    } else {
      notify('danger', Translator.trans('project_plan.item.remove_error_message'));
    }
  }).error(function() {
    notify('danger', Translator.trans('project_plan.item.remove_error_message'));
  });
});

$('.js-data-popover').popover({
  html: true,
  trigger: 'hover',
  placement: 'top',
  content: $('.popover-content').html()
});
