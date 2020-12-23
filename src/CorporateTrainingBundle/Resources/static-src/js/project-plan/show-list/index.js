import ModalController from '../../common/modal-controller';
import notify  from 'common/notify';

new ModalController();

const $form = $('#project-plan-search-form');
$('#timeStatus').change(function() {
  $form.submit();
});

$('.name-search').click(function () {
  $form.submit();
});

$('#enrollment').change(function() {
  $form.submit();
});

$('.js-category').click(function () {
  $('#categoryId').val($(this).find('a').data('id'));
  $form.submit();
});

$('[name="nameLike"]').bind('keypress', function(event) {
  if (event.keyCode == '13') {
    event.preventDefault();
    $form.submit();
  }

});

$form.on('click', '.js-plan-state', (event)=> {
  const val = $(event.currentTarget).data('val');

  $('.js-time-state').val(val);
  $form.submit();
});