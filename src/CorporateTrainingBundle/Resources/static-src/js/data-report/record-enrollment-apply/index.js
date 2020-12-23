import specialAjaxPage from './special-ajax-page';
import chapterAnimate from '../../common/chapter-animate';
import tabBlock from '../../common/tab-block';

class Page {
  constructor() {
    this.init();
  }

  init() {
    echo.init();
    this.initEchart();
    this.initpieChart();
    this.initCourseCount();
    this.initCourseItem();
    this.initToolTip();
  }

  initEchart() {
    if ($('.js-pie-chart-1').length <= 0) {
      return false; 
    }

    let categoryName = $('.js-category-name');
    let categoryCount = $('.js-category-count');
    let nameArray = [];
    let countArray = [];

    categoryName.each((i, element) => {
      nameArray.push($.trim($(element).text()));
    });
    categoryCount.each((i, element) => {
      countArray.push($.trim($(element).text()));
    });

    let options = {
      title: {
        text: Translator.trans('my.department.data_report.top_5_taxonomy'),
        x: 'center'
      },
      tooltip: {
        trigger: 'item',
        formatter: function(params) {
          let tips;
          let datas = params.data;
          if (datas.name.length > 10) {
            tips = datas.name.substring(0, 10) + '...';
          } else {
            tips = datas.name;
          }
          let res = params.seriesName + '<br/>' + tips + ' : ' + datas.value + '(' + params.percent + '%)';
          return res;
        }
      },
      calculable: false,

      legend: {
        x: 'center',
        y: 'bottom',
        data: nameArray,
        formatter: function(name) {
          return name.length > 10 ? (name.substring(0, 10) + '...') : name;
        }
      },
      series: [{
        name: Translator.trans('my.department.data_report.taxonomy'),
        type: 'pie',
        radius: '55%',
        center: ['50%', '50%'],
        itemStyle: {
          normal: {
            label: {
              show: false
            },
            labelLine: {
              show: false
            }
          }
        },
        data: [{
          value: countArray[0],
          name: nameArray[0]
        }, {
          value: countArray[1],
          name: nameArray[1]
        }, {
          value: countArray[2],
          name: nameArray[2]
        }, {
          value: countArray[3],
          name: nameArray[3]
        }, {
          value: countArray[4],
          name: nameArray[4]
        }],
      }],
    };

    let myChart = echarts.init(document.querySelector('.js-pie-chart-1'));

    myChart.setOption(options);

    $(window).on('resize', function() {
      myChart.resize();
    });
  }

  initpieChart() {
    let colorPrimary = $('.js-pie-chart-hover').css('color');
    $('.js-chart').easyPieChart({
      animate: 2000,
      size: 60,
      barColor: colorPrimary,
      trackColor: '#ededed' ,
      scaleLength: 0 ,
      lineWidth: 4,
      onStart: function() {
        $('.js-pie-chart-hover').removeClass('hidden');
      }
    });
  }

  initToolTip() {
    $('[data-toggle="tooltip"]').tooltip();
  }

  initCourseCount() {
    let $courseItem = $('.js-course-count');
    for (let i = 0; i < $courseItem.length; i++) {
      new tabBlock({ element: $courseItem[i]});
    }
  }

  initCourseItem() {
    if ($('.js-class-course-list').length <= 0) {
      return false;
    }

    let $courseItem = $('.js-course-item');
    for (let i = 0; i < $courseItem.length; i++) {
      new specialAjaxPage({ element: $courseItem[i]}).init();
      new chapterAnimate({ element: $courseItem[i]});
    }  
  }
}

new Page();
    