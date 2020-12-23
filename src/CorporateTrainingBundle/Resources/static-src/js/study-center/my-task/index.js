import { isMobileDevice } from 'common/utils';
import ajaxPageTask from './ajax-page-task';
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
  }

  initAjax($element) {

    $element.each(function() {
      new ajaxPageTask({ element: $(this)}).init();
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
      onStart: function() {
        $('.js-pie-chart-hover').removeClass('hidden');
      }
    });
  }

  initIntro() {
    if(isMobileDevice()) {
      return;
    }

    let hasReadGuide = $('#step1').data('hasReadGuide');
    if(hasReadGuide) {
      return;
    }

    let lastStepElem;
    $('#nav').find('a').each((i, element) => {
      if ($(element).text() === '课程广场 ') {
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
      nextLabel: '下一步 <i class="es-icon es-icon-arrowforward"></i>',
      skipLabel: '<i class="es-icon es-icon-close01"></i>',
      doneLabel: '开始学习',
      hidePrev: true,
      hideNext: true,
      tooltipClass: 'study-center-tooltip-intro',
      showStepNumbers: false,
      exitOnOverlayClick: false,
      steps: stepContent,
    });

    let completeGuide = function() {
      $.post($('#step1').data('completeGuideUrl'));
    };

    intro.onchange(function(elem) {
      if ( (!lastStepElem && elem == $('#step4')[0]) || elem == lastStepElem) {
        $('.introjs-button.introjs-skipbutton').addClass('is-last');
      } else {
        $('.introjs-button.introjs-skipbutton').removeClass('is-last');
      }
    }).onexit(function(elem) {
      completeGuide();
    }).oncomplete(function(elem) {
      completeGuide();
    }).start();
  }
}

new Page();

const $searchWrap = $('.js-search-wrap');
new ajaxLink({element: $searchWrap});

