initEvent();

function initEvent() {
  $('.js-save-btn').click(() => {
    if (!$('.js-save-btn').hasClass('disabled')) {
      $(this).button('loading');
      $.post($('#offline-activity-attend-form').attr('action'), $('#offline-activity-attend-form').serialize(), function (json) {
        window.location.reload();
      }, 'json');
    }
  });
}
