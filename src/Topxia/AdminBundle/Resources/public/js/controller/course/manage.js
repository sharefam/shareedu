define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  require('../widget/category-select').run('course');
  var CourseSetClone = require('../course-set/clone');

  exports.run = function (options) {

    var csl = new CourseSetClone();

    let $dataList = $('.data-list');
    $dataList.on('click', '.cancel-recommend-course', function() {
      $.post($(this).data('url'), function (html) {
        let $tr = $(html);
        $dataList.find('#' + $tr.attr('id')).replaceWith(html);
        Notify.success(Translator.trans('admin.course.cancel_recommend_success'));
      });
    });

    $dataList.on('click', '.js-course-set-clone', function() {
      let $this = $(this);
      $.ajax({
        type: 'get',
        url: $this.data('url'),
        success: function(resp) {
          $('#modal').html(resp).modal();
        }
      });
    });

    $dataList.on('click', '.close-course', function() {
      if (!confirm(Translator.trans('admin.course.close_confirm_message'))) return false;
      $.post($(this).data('url'), function (html) {
        let $tr = $(html);
        $dataList.find('#' + $tr.attr('id')).replaceWith(html);
        Notify.success(Translator.trans('admin.course.close_success'));
      });
    });

    $dataList.on('click', '.publish-course', function() {
      if (!confirm(Translator.trans('admin.course.publish_confirm_message'))) {
        return false;
      }
      $.post($(this).data('url'), function(response) {
        if (!response['success'] && response['message']) {
          Notify.danger(response['message']);
        } else {
          let $tr = $(response.message);
          $dataList.find('#' + $tr.attr('id')).replaceWith($tr);
          Notify.success(Translator.trans('admin.course.publish_success'));
        }
      }).error(function(e) {
        let res = e.responseJSON.error.message || '未知错误';
        Notify.danger(res);
      });
    });

    $dataList.on('click', '.delete-course', function() {
      if (!confirm(Translator.trans('admin.course.delete_confirm_message'))) {
        return;
      }

      let $this = $(this);
      let $tr = $this.parents('tr');
      $.post($this.data('url'), function(data) {
        if (data.code > 0) {
          Notify.danger(data.message);
        } else if (data.code == 0) {
          $tr.remove();
          Notify.success(data.message);
        } else {
          $('#modal').modal('show').html(data);
        }
      });
    });

    $dataList.find('.copy-course[data-type="live"]').tooltip();

    $dataList.on('click', '.copy-course[data-type="live"]', function(e) {
      e.stopPropagation();
    });

    if ($('#course_tags').length > 0) {
      $('#course_tags').select2({
        ajax: {
          url: app.arguments.tagMatchUrl + '#',
          dataType: 'json',
          quietMillis: 100,
          data: function (term, page) {
            return {
              q: term,
              page_limit: 10
            };
          },
          results: function (data) {
            let results = [];

            $.each(data, function (index, item) {
              results.push({
                id: item.name,
                name: item.name
              });
            });

            return {
              results: results
            };
          }
        },
        initSelection: function (element, callback) {
          let data = [];
          $(element.val().split(',')).each(function () {
            data.push({
              id: this,
              name: this
            });
          });
          callback(data);
        },
        formatSelection: function (item) {
          return item.name;
        },
        formatResult: function (item) {
          return item.name;
        },
        width: 'off',
        multiple: true,
        maximumSelectionSize: 20,
        placeholder: Translator.trans('admin.course.tags_placeholder'),
        width: '162px',
        multiple: true,
        createSearchChoice: function () {
          return null;
        },
        maximumSelectionSize: 20
      });
    }
  };

});
