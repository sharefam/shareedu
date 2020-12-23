$('ul.learning-status-tab').find('li').on('click', function () {
  $(this).addClass('active');
  $(this).siblings('li').removeClass('active');
  if ($(this).hasClass('course-record')) {
    $('.course-record-list').css('display', 'block');
    $('.course-record-list').addClass('highlight');
    $('.offline-course-record-list').css('display', 'none');
    $('.projectplan-record-list').css('display', 'none');
  } else if ($(this).hasClass('offline-course-record')) {
    $('.course-record-list').css('display', 'none');
    $('.offline-course-record-list').css('display', 'block');
    $('.offline-course-record-list').addClass('highlight');
    $('.projectplan-record-list').css('display', 'none');
  } else if ($(this).hasClass('projectplan-record')) {
    $('.course-record-list').css('display', 'none');
    $('.offline-course-record-list').css('display', 'none');
    $('.projectplan-record-list').css('display', 'block');
    $('.projectplan-record-list').addClass('highlight');
  }
});
$('[data-toggle="tooltip"]').tooltip({
  html: true,
});

$('[data-toggle="popover"]').popover();
