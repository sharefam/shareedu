$('.js-dashbord-tab').on('click', 'li', (event)=> {
  const $this = $(event.currentTarget);
  const type = $this.data('type');
  const onLineData = $(`.js-${type}-year`).data('onLine');
  const offLineData = $(`.js-${type}-year`).data('offLine');

  $this.addClass('active').siblings().removeClass('active');
  $('.js-on-line').text(onLineData);
  $('.js-off-line').text(offLineData);
});
