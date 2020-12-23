import 'common/tabs-lavalamp/index';
import BatchSelectInCookie from 'app/common/widget/batch-select-in-cookie';
import tabBlock from '../../../common/tab-block';
new tabBlock({ element: '#courses-picker-body', mode: 'click', cb: function(e) {}});
import notify from 'common/notify';

export default class Base {
  constructor() {
    this.init();

  }

  init(){
    let batchSelectInCookie = new BatchSelectInCookie($('#courses-picker-body'), 'postCourseIds');
    let ids = [];
    batchSelectInCookie._clearCookie();
    let self = this;
    $('#js-save').on('click', function () {
      $('#sure').button('submiting').addClass('disabled');
      let courseIds = $('#itemIds').val();
      if(courseIds == ''){
        notify('danger', Translator.trans('project_plan.online_course.select_course_placeholder'));
        return;
      }
      let ids = courseIds.split(',');
      $.post($(this).data('url'), {courseIds:ids}, function (data) {
        $('#attachment-modal', window.parent.document).modal('hide');
        window.location.reload();
      });
    });

    $('.js-tab-link').on('click', function () {
      let  url = $(this).data('url');
      let self = $(this);
      if (typeof (url) !== 'undefined') {
        $.post(url,function (data) {
          $('.js-manage-data-list').html('');
          $('.js-use-permission-data-list').html('');
          $('.courses-list').find('.js-courses-count').remove();
          $(`.${self.data('type')}`).html(data);
          let batchSelectInCookie = new BatchSelectInCookie($('#courses-picker-body'), 'postCourseIds');
          batchSelectInCookie._clearCookie();

        });
      }
    });

    $('.courses-list').on('click', '#search',function () {
      let selfDataList = $(this).parents('.data-list');
      $.post($(this).data('url'), $(this).parents('.form-search').serialize(), function (data) {
        $(`.${selfDataList.data('type')}`).html(data);
        new BatchSelectInCookie($('#courses-picker-body'), 'postCourseIds');
      });

    });

    $('.courses-list').on('click', '.pagination li', function () {
      var url = $(this).data('url');
      let selfDataList = $(this).parents('.data-list');

      if (typeof (url) !== 'undefined') {
        $.post(url, $(this).parents('.form-search').serialize(), function (data) {
          $(`.${selfDataList.data('type')}`).html(data);
          new BatchSelectInCookie($('#courses-picker-body'), 'postCourseIds');
        });
      }
    });

    $('.courses-list').on('click','#all-courses', function () {
      let selfDataList = $(this).parents('.data-list');
      $.post($(this).data('url'), function (data) {
        $(`.${selfDataList.data('type')}`).html(data);
        new BatchSelectInCookie($('#courses-picker-body'), 'postCourseIds');
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

        ids = $.grep(ids, function (val, key) {

          if (val != sid + ':' + id)
            return true;
        }, false);
      } else {
        $course.addClass('select');


        ids.push(sid + ':' + id);
      }
    });
  }

  rendItem(opts){
    return `<li class="project-plan-sortable-item" data-course-set-id="${opts.id}" data-id="${opts.defaultCourseId}">
                    <i class="project-plan-sortable-item__icon es-icon es-icon-yidong mrl gray-medium vertical-middle"></i>
                    <span class="project-plan-sortable-item__info">
                      课程：${opts.title}
                    </span>
                    <span class="gray-medium">；</span>
                    <span class="project-plan-sortable-item__info">编号：${opts.defaultCourseId}</span>
                        <a class="project-plan-sortable-item__close-btn js-close-icon" href="javascript:;">
                            <i class="es-icon es-icon-close01 pull-right "></i>
                        </a>
                </li>`;
  }

}

new Base();