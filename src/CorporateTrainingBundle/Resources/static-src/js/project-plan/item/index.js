let modalController = $('#modal').get(0).__relation__;

$('.js-unit-addition-select').on('click', '.js-unit-addition-select-item', function() {
  $(this).addClass('active');
  const url = $(this).data('url');
  modalController.remote(url);
});

$('[data-toggle=\'popover\']').popover();