class Page {
  constructor() {
    this.init();
  }

  init() {
    this.initEchart();
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
        text: Translator.trans('study_center.record.options_title'),
        x: 'center',
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
        name: Translator.trans('study_center.record.taxonomy'),
        type: 'pie',
        radius: '50%',
        center: ['50%', '50%'],
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

    $('.js-data-popover').popover({
      html: true,
      trigger: 'hover',
      placement: 'bottom',
      template: '<div class="popover tata-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
      content: function() {
        return $(this).siblings('.popover-content').html();
      }
    });
  }
}

new Page();
