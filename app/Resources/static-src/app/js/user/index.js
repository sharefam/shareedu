$('.follow-btn').on('click', function() {
  const $this = $(this);
  $.post($this.data('url'), function() {
    $this.hide();
    $this.next('.unfollow-btn').show();
  });
});


$('.unfollow-btn').on('click', function() {
  const $this = $(this);
  $.post($this.data('url'), function() {
    $this.hide();
    $this.prev('.follow-btn').show();
  });
});

$('.user-center-header').blurr({height:220}).css({background:'rgba(0, 0, 0, 0.3)'});

$('.set_mask').css({'zIndex':'101'});
echo.init();