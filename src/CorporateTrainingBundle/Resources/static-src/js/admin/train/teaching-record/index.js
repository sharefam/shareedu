import '../../../common/echarts-theme';
import ajaxLink from '../../../common/ajax-link';
import CheckTreeviewInput from '../../../common/CheckTreeview-input';

function getDefaultopts() {
  return {
    color: ['#3398DB'],
    tooltip : {
      trigger: 'axis',
      axisPointer : {            // 坐标轴指示器，坐标轴触发有效
        type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
      },
      formatter: function(datas)
      {
        let res = Translator.trans('my.teaching_record.chart.survey')+datas[0].name + '<br/>', val;
        for(let i = 0, length = datas.length; i < length; i++) {
          val = (datas[i].value);
          res += datas[i].seriesName + '：' + val + '<br/>';
        }
        return res;
      }
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: ['0~3', '3~3.5', '3.5~4', '4~4.5', '4.5~5']
    },
    yAxis: {
      type: 'value',
      minInterval : 1,
    },
    series: {
      name: Translator.trans('my.teaching_record.chart.series_title'),
      barWidth: '50',
      data: [0,0,0,0,0],
      type: 'bar'
    }
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
    this.chart.setOption(this.options,true);
    let nav = $('.c-course-rank-list').find('.js-nav');

    nav.on('click', 'li', function (e) {
      const $self = $(this);
      $self.siblings().removeClass('active');
      $self.addClass('active');
    });

  }
}

function initChart(elem) {

  const myChart = new lineChart({elem: elem});

  let {options} = myChart;


  const chart = $(elem).closest('.c-data-learn-time-chart').find('.js-nav');
  let url = chart.find('li.active').data('url');

  getData(myChart, url);

  chart.on('click', 'li', function (e) {
    const $self = $(this);
    const url = $self.data('url');
    $self.siblings().removeClass('active');
    $self.addClass('active');
    getData(myChart, url);
  });
  //获取数据 todo
  function getData(chart, url) {
    chart.chart.showLoading();
    $.get(url)
      .done(function (data) {
        options.series.data = data.series.data;
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
if($('#survey-data').length > 0) {
  initChart(document.getElementById('survey-data'));
}

const $searchWrap = $('.js-search-wrap');

for (let i = 0; i < $searchWrap.length; i++) {
  new ajaxLink({element: $searchWrap[i]});
}

(function initTreeviewInput() {
  let $treeview = $('.js-treeview-select-wrap');
  for (let i = 0; i < $treeview.length; i++) {
    new CheckTreeviewInput({ $elem: $treeview.eq(i) });
  }
})();
