import 'common/tabs-lavalamp/index';
import BatchSelectInCookie from 'app/common/widget/batch-select-in-cookie';
import tabBlock from '../../common/tab-block';
new tabBlock({ element: '#courses-picker-body', mode: 'click', cb: function(e) {}});

let batchSelectInCookie = new BatchSelectInCookie($('#courses-picker-body'), 'classroomCourseIds');
var ids = [];
batchSelectInCookie._clearCookie();

$('[data-toggle="tooltip"]').tooltip();
$('.js-tab-link').on('click', function () {
  let  url = $(this).data('url');
  let self = $(this);
  if (typeof (url) !== 'undefined') {
    $.post(url,function (data) {
      $('.js-manage-data-list').html('');
      $('.js-use-permission-data-list').html('');
      $('.courses-list').find('.js-courses-count').remove();
      $(`.${self.data('type')}`).html(data);
      batchSelectInCookie = new BatchSelectInCookie($('#courses-picker-body'), 'classroomCourseIds');
      batchSelectInCookie._clearCookie();

    });
  }
});

$('#sure').on('click', function () {
  $('#sure').button('submiting').addClass('disabled');
  let courseIds = $('#itemIds').val();
  let ids = courseIds.split(',');
  for (var i = 0; i < ids.length; i++) {
    var sid = ids[i];
    var id = ids[i];
    ids[i] = sid + ':' + id;
  }

  $.ajax({
    type: 'post',
    url: $('#sure').data('url'),
    data: 'ids=' + ids,
    async: false,
    success: function (data) {

      $('.modal').modal('hide');
      window.location.reload();
      batchSelectInCookie._clearCookie();
    }

  });

});

$('.courses-list').on('click', '#search',function () {
  let selfDataList = $(this).parents('.data-list');
  $.post($(this).data('url'), $(this).parents('.form-search').serialize(), function (data) {
    $(`.${selfDataList.data('type')}`).html(data);
    new BatchSelectInCookie($('#courses-picker-body'), 'classroomCourseIds');
  });

});

$('.courses-list').on('click', '.pagination li', function () {
  var url = $(this).data('url');
  let selfDataList = $(this).parents('.data-list');

  if (typeof (url) !== 'undefined') {
    $.post(url, $(this).parents('.form-search').serialize(), function (data) {
      $(`.${selfDataList.data('type')}`).html(data);
      new BatchSelectInCookie($('#courses-picker-body'), 'classroomCourseIds');
    });
  }
});

$('.courses-list').on('click','#all-courses', function () {
  let selfDataList = $(this).parents('.data-list');
  $.post($(this).data('url'), function (data) {
    $(`.${selfDataList.data('type')}`).html(data);
    new BatchSelectInCookie($('#courses-picker-body'), 'classroomCourseIds');
  });

});

$('.courses-list').on('click', '.course-item-cbx', function () {

  var $course = $(this).parent();
  var sid = $course.data('id');//courseSet.id

  if ($course.hasClass('enabled')) {
    return;
  }
  var id = $('#course-select-' + sid).val();
  if ($course.hasClass('select')) {
    $course.removeClass('select');
    // $('.course-metas-'+sid).hide();

    ids = $.grep(ids, function (val, key) {

      if (val != sid + ':' + id)
        return true;
    }, false);
  } else {
    $course.addClass('select');

    // $('.course-metas-'+sid).show();

    ids.push(sid + ':' + id);
  }
});