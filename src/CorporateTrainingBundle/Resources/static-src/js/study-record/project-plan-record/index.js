import ajaxPageTask from './ajax-page-project';
import chapterAnimate from '../../common/chapter-animate';
import notify from 'common/notify';

class Page {
  constructor() {
    this.init();
  }
  init() {
    echo.init();
    this.initAjax($('.js-time-item'));
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
      },
      onStep: function (from, to, percent) {

        if (Math.round(percent) == 100) {
          $(this.el).addClass('done');
        }
        $(this.el).find('.js-course__percent__percent').html(Math.round(percent) + '%');
      }
    });
  }
}

new Page();

