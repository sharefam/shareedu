let $btn = $('.js-resource-btn');
$btn.on('click','.js-tab-link',function (e) {
  let type = $(e.currentTarget).data('type');
  let url = $(e.currentTarget).data('url');
  if(type === 'projectPlan'){
    $('.js-classroom-tab').removeClass('active');
    $('.js-project-tab').addClass('active');
  }else{
    $('.js-project-tab').removeClass('active');
    $('.js-classroom-tab').addClass('active');
  }
  $.post(url,function (html) {
    $('.js-course-list').html(html);
  });
});

$('.js-course-list').on('click', '.pagination li', function () {
  let url = $(this).data('url');
  if (typeof (url) !== 'undefined') {
    $.post(url, function (data) {
      $('.js-course-list').html(data);
    });
  }
});