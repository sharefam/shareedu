import { isMobileDevice } from 'common/utils';
import ajaxPageTask from './ajax-page-project';
import chapterAnimate from '../../common/chapter-animate';
import tabBlock from '../../common/tab-block';
import ajaxLink from '../../common/ajax-link';
import notify from 'common/notify';

class Page {
  constructor() {
    this.init();
  }

  init() {
    echo.init();

    this.initIntro();
    this.initAjax($('.js-course-item'));
    this.initToolTip();
    this.initEasyPieChart($('.js-chart'));
    this.initLoadingMore();
  }

  initAjax($element) {

    $element.each(function () {
      new ajaxPageTask({element: $(this)}).init();
      new chapterAnimate({element: $(this)});
    });
  }

  initToolTip() {
    $('[data-toggle="tooltip"]').tooltip({
      container: 'body'
    });
  }

  initEasyPieChart($element) {
    let colorPrimary = $('.js-pie-chart-hover').css('color');
    $element.easyPieChart({
      animate: 2000,
      size: 60,
      barColor: colorPrimary,
      trackColor: '#e1e1e1',
      scaleLength: 0,
      lineWidth: 5,
      onStart: function () {
        $('.js-pie-chart-hover').removeClass('hidden');
      },
      onStep: function (from, to, percent) {

        if (Math.round(percent) == 100) {
          $(this.el).addClass('done');
        }
        $(this.el).find('.js-c-ctr-task-item__percent').html(Math.round(percent) + '%');
      }
    });
  }

  initIntro() {
    if(isMobileDevice()) {
      return;
    }

    let hasReadGuide = $('#step1').data('hasReadGuide');
    if (hasReadGuide) {
      return;
    }

    let lastStepElem;
    $('#nav').find('a').each((i, element) => {
      if ($(element).text() === Translator.trans('study_center.newbie_guide.course_square')) {
        lastStepElem = $(element)[0];
      }
    });

    let intro = introJs();
    let stepContent = [
      {
        intro: `<p class="study-center-tooltip-title">${Translator.trans('study_center.newbie_guide.title')}</p><p style="font-size: 30px;">:)</p><p>${Translator.trans('study_center.newbie_guide.message')}</p>`
      },
      {
        element: '#step1',
        intro: `<p class="study-center-tooltip-title">${Translator.trans('study_center.newbie_guide.my_task')}</p><p  style="font-size: 16px;">${Translator.trans('study_center.newbie_guide.my_task_message')}</p>`
      },
      {
        element: '#step2',
        intro: `<p class="study-center-tooltip-title">${Translator.trans('study_center.newbie_guide.my_courses')}</p><p  style="font-size: 16px;">${Translator.trans('study_center.newbie_guide.my_courses_message')}</p>`
      },
      {
        element: '#step4',
        intro: `<p class="study-center-tooltip-title">${Translator.trans('study_center.newbie_guide.department')}</p><p  style="font-size: 16px;">${Translator.trans('study_center.newbie_guide.department_message')}</p>`
      }
    ];

    if (lastStepElem) {
      let finalStep = {
        element: lastStepElem,
        intro: `<p class="study-center-tooltip-title">${Translator.trans('study_center.newbie_guide.course_square')}</p><p  style="font-size: 16px;">${Translator.trans('study_center.newbie_guide.course_square_message')}</p>`
      };
      stepContent.push(finalStep);
    }

    intro.setOptions({
      showBullets: false,
      nextLabel: `${Translator.trans('study_center.newbie_guide.btn_next_step')} <i class="es-icon es-icon-arrowforward"></i>`,
      skipLabel: '<i class="es-icon es-icon-close01"></i>',
      doneLabel: Translator.trans('study_center.newbie_guide.btn_start_study'),
      hidePrev: true,
      hideNext: true,
      tooltipClass: 'study-center-tooltip-intro',
      showStepNumbers: false,
      exitOnOverlayClick: false,
      steps: stepContent,
    });

    let completeGuide = function () {
      $.post($('#step1').data('completeGuideUrl'));
    };

    intro.onchange(function (elem) {
      if ((!lastStepElem && elem == $('#step4')[0]) || elem == lastStepElem) {
        $('.introjs-button.introjs-skipbutton').addClass('is-last');
      } else {
        $('.introjs-button.introjs-skipbutton').removeClass('is-last');
      }
    }).onexit(function (elem) {
      completeGuide();
    }).oncomplete(function (elem) {
      completeGuide();
    }).start();
  }

  initLoadingMore() {

    let $taskBtn = $('.js-task-btn');

    if ($taskBtn.length <= 0) {
      return false;
    }

    let total = $taskBtn.data('total');
    let baseUrl = $taskBtn.data('url');
    let currentIndex = 0;
    let numPerPage = 20;
    let self = this;

    $(window).scroll(function () {
      if ($(window).scrollTop() + $(window).height() == $(document).height()) {
        $taskBtn.click();
      }
    });

    $taskBtn.on('click', function (e) {
      if ($(this).hasClass('disabled')) {
        return;
      }

      $taskBtn.html(Translator.trans('study_center.newbie_guide.loading'));
      $taskBtn.addClass('disabled');

      let url = baseUrl + '?start=' + currentIndex;

      $.ajax({
        url: url,
        type: 'get',
        dataType: 'html',
      }).success((data) => {

        let $data = $(data);
        let $itemArray = $data.filter(function (index, item) {
          return $(item).hasClass('js-course-item');
        });

        $('.js-class-course-list .o-ctr-task-list__bd').append($data);

        self.initAjax($itemArray);
        self.initEasyPieChart($itemArray.find('.js-chart'));

        currentIndex = currentIndex + numPerPage;
        if (currentIndex >= total) {
          $taskBtn.remove();
        }
      }).fail(function (err) {
        notify('danger', Translator.trans('study_center.newbie_guide.load_error'));
      }).complete(function () {
        $taskBtn.html(Translator.trans('study_center.newbie_guide.load_more'));
        $taskBtn.removeClass('disabled');
      });
    });

    $taskBtn.click();

  }
}

new Page();

const $searchWrap = $('.js-search-wrap');
new ajaxLink({element: $searchWrap});

