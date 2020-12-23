import ajaxLink from '../../common/ajax-link';
import CheckTreeviewInput from '../../common/CheckTreeview-input';
import '../../common/echarts-theme';

function getDefaultopts() {
  return {
    title: {
      text: ''
    },
    tooltip: {
      trigger: 'axis'
    },
    legend: {
      top: 'middle',
      left: 'right',
      data: []
    },
    grid: {
      left: '20px',
      bottom: '20px',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      splitLine: {
        show: false
      },
      axisTick: {
        show: false
      },
      axisLine: {
      },
      data: []
    },
    yAxis: {
      type: 'value',
      minInterval: [],
      splitLine: {
        show: true,
        lineStyle: {
          type: 'dashed'
        }
      },
      axisLine: {
        show: false
      },
      axisTick: {
        show: false
      }
    },
    series: []
  };
}

class lineChart {
  constructor(props) {
    this.elem = props.elem;
    this.options = getDefaultopts();
    this.init();
  }

  init() {
    this.chart = echarts.init(this.elem, 'corporateTraining');
    this.chart.setOption(this.options);
  }
}

function initChart(elem) {

  const myChart = new lineChart({elem: elem});

  let {options} = myChart;


  const $nav = $(elem).closest('.c-data-learn-time-chart').find('.js-nav');
  const url = $nav.find('li.active').data('url');

  getData(myChart, url);

  $nav.on('click', 'li', function (e) {
    const $self = $(this);
    const url = $self.data('url');
    $self.siblings().removeClass('active');
    $self.addClass('active');
    getData(myChart, url);
  });

  //获取数据 todo
  function getData(chart, url) {
    chart.chart.showLoading();
    $.post(url, $('.o-department-learn-data__form').serialize())
      .done(function (data) {
        options.xAxis.data = data.xAxis.data;
        options.yAxis.minInterval = data.yAxis.minInterval;
        options.series = data.series;
        chart.chart.setOption(options);
        chart.chart.hideLoading();
      })
      .error(function (err) {
        console.error(err);
      });
  }

  $(window).on('resize', function() {
    myChart.chart.resize();
  });

  return myChart;
}

initChart(document.getElementById('learning-time'));

initChart(document.getElementById('learning-person'));

const $searchWrap = $('.js-search-wrap');

for (let i = 0; i < $searchWrap.length; i++) {
  new ajaxLink({element: $searchWrap[i], data: $('.o-department-learn-data__form').serialize()});
}

(function initTreeviewInput() {
  let $treeview = $('.js-treeview-select-wrap');
  for (let i = 0; i < $treeview.length; i++) {
    new CheckTreeviewInput({ $elem: $treeview.eq(i) });
  }
})();

$('.js-tab-sec').on('mouseover', '.es-icon-more', function () {
  $(this).popover('show');
}).on('mouseleave', '.es-icon-more', function () {
  $(this).popover('destroy');
});