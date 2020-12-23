initEvent();

function initEvent() {
  $('.js-save-btn').click(() => {
    if (!$('.js-save-btn').hasClass('disabled')) {
      $('.js-save-btn').button('loading');
      $.post($('#offline-activity-apply-form').attr('action'), $('#offline-activity-apply-form').serialize(), function (json) {
        window.location.reload();
      }, 'json');
    }
  });
}
