define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Cookie = require('cookie');

  exports.run = function () {
    var deleteVacancy = function (array) {
      $.each(array, function (index, value) {
        if (value == '' || value == null) {
          array.splice(index, 1);
        }
        ;
      });

      return array;
    }

    var initChecked = function (array) {
      var length = $('.batch-item').length;
      var checked_count = 0;
      courseIds = deleteVacancy(array);

      $('#selected-count').text(array.length);
      $('#courseIds').val(Cookie.get('courseIds'));

      $.each(array, function (index, value) {
        $('#batch-item-' + value).prop('checked', true);
      });

      $('.batch-item').each(function () {
        if ($(this).is(':checked')) {
          checked_count++;
        }
        ;

        if (length == checked_count) {
          $('.batch-select').prop('checked', true);
        } else {
          $('.batch-select').prop('checked', false);
        }
      });
    }

    $('.courses-list').on('click', '.pagination li', function () {
      var url = $(this).data('url');
      if (typeof(url) !== 'undefined') {
        $.post(url, $('#post-course-search-form').serialize(), function (data) {
          $('.courses-list').html(data);
        });
      }
    });

    $('#search').on('click', function () {
      $.post($(this).data('url'), $('#post-course-search-form').serialize(), function (data) {
        $('.courses-list').html(data);
        initChecked(Cookie.get('courseIds').split(','));
      });
    });

    var courseIds = new Array();
    if (typeof(Cookie.get('courseIds')) != 'undefined') {
      courseIds = deleteVacancy(Cookie.get('courseIds').split(','));
    }
    ;
    Cookie.set('courseIds', courseIds);

    if (Cookie.get('courseIds').length > 0) {
      initChecked(Cookie.get('courseIds').split(','));
    }
    ;

    $('.courses-list').on('click', '.batch-select', function () {
      var $selectdElement = $(this);

      if (Cookie.get('courseIds').length > 0) {
        courseIds = deleteVacancy(Cookie.get('courseIds').split(','));
      }
      ;

      if ($selectdElement.prop('checked') == true) {
        $('.batch-item').prop('checked', true);
        $('.batch-item').each(function (index, el) {
          pushArrayValue(courseIds, $(this).val());
        });
      } else {
        $('.batch-item').prop('checked', false);
        $('.batch-item').each(function (index, el) {
          popArrayValue(courseIds, $(this).val());
        });
      }

      $('#selected-count').text(courseIds.length);
      Cookie.set('courseIds', courseIds);
      $('#courseIds').val(Cookie.get('courseIds'));
    });

    $('.courses-list').on('click', '.batch-item', function () {
      var length = $('.batch-item').length;
      var checked_count = 0;

      if (Cookie.get('courseIds').length > 0) {
        courseIds = deleteVacancy(Cookie.get('courseIds').split(','));
      }
      ;

      if ($(this).prop('checked') == true) {
        pushArrayValue(courseIds, $(this).val());
      } else {
        popArrayValue(courseIds, $(this).val());
      }

      $('.batch-item').each(function () {
        if ($(this).is(':checked')) {
          checked_count++;
        }
        ;

        if (length == checked_count) {
          $('.batch-select').prop('checked', true);
        } else {
          $('.batch-select').prop('checked', false);
        }
      });

      $('#selected-count').text(courseIds.length);
      Cookie.set('courseIds', courseIds);
      $('#courseIds').val(Cookie.get('courseIds'));
    });

    $('#clear-cookie').click(function () {
      courseIds = Cookie.get('courseIds').split(',');
      courseIds.splice(0, courseIds.length);
      Cookie.set('courseIds', courseIds);
      $('#selected-count').text(0);
      $('#courseIds').val(null);
      $('input[type=checkbox]').prop('checked', false);
    });

    $('#post-course-btn').click(function (e) {
      var courseIds = $('#courseIds').val();
      var courseCount = courseIds.split(",");

      if (existChecked($('.batch-item')) && courseCount <= 0) {
        Notify.warning(Translator.trans('admin.post_course.add_empty'));
        e.stopImmediatePropagation();
        return;
      }
      ;

      $(this).button('submiting').addClass('disabled');

      $.post($(this).data('url'), {courseIds: courseIds}, function (result) {
        if (result.success) {
          Notify.success(Translator.trans('admin.post_course.add_course_success'));
          window.location.reload();
        } else {
          Notify.danger(Translator.trans('admin.post_course.add_course_error'));
        }
      }).success(function () {
        courseIds = Cookie.get('courseIds').split(',');
        courseIds.splice(0, courseIds.length);
        Cookie.set('courseIds', courseIds);
      }).error(function () {
        Notify.danger(Translator.trans('admin.post_course.add_course_error'));
      });
    });

    var pushArrayValue = function (array, targetValue) {
      var isExist = false;
      $.each(array, function (index, value) {
        if (value == targetValue) {
          isExist = true;
          return;
        }
        ;
      });

      if (!isExist && !isNaN(targetValue)) {
        array.push(targetValue);
      }
      ;
    }

    var popArrayValue = function (array, targetValue) {
      $.each(array, function (index, value) {
        if (value == targetValue) {
          array.splice(index, 1);
        }
        ;
      });
    }

    var existChecked = function (element) {
      var status = true;
      $(element).each(function () {
        if ($(this).prop('checked') == true) {
          status = false;
          return;
        }
        ;
      });

      return status;
    }
  }
});
