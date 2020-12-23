import BatchSelectInCookie from 'app/common/widget/batch-select-in-cookie';

let batchSelectInCookie = new BatchSelectInCookie($('#courses-picker-body'), 'classroomCourseIds');
var ids = [];

$('[data-toggle="tooltip"]').tooltip();

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

$('#search').on('click', function () {

  if ($('[name=key]').val() != '') {

    $.post($(this).data('url'), $('.form-search').serialize(), function (data) {

      $('.courses-list').html(data);
      new BatchSelectInCookie($('#courses-picker-body'), 'classroomCourseIds');
    });
  }

});

$('.courses-list').on('click', '.pagination li', function () {
  var url = $(this).data('url');
  if (typeof (url) !== 'undefined') {
    $.post(url, $('.form-search').serialize(), function (data) {
      $('.courses-list').html(data);
      new BatchSelectInCookie($('#courses-picker-body'), 'classroomCourseIds');
    });
  }
});

$('#enterSearch').keydown(function (event) {

  // if (event.keyCode == 13) {

  //   $.post($(this).data('url'), $('.form-search').serialize(), function (data) {

  //     $('.courses-list').html(data);

  //   });
  //   return false;
  // }
});

$('#all-courses').on('click', function () {

  $.post($(this).data('url'), $('.form-search').serialize(), function (data) {
    $('#modal').html(data);
    new BatchSelectInCookie($('#courses-picker-body'), 'classroomCourseIds');
  });

});

// $('.js-course-select').on('change', function () {
//   var id = $(this).val();
//   var sid = $(this).attr('id').split('-')[2];
//   for (var i = 0; i < ids.length; i++) {
//     var idArr = ids[i].split(':');
//     if (idArr[0] == sid) {
//       ids[i] = sid + ':' + id;
//       break;
//     }
//   }
//
//   var price = $(this).find(':selected').data('price');
//   $('.js-price-' + sid).html(price);
// });

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