import ajaxLink from '../common/ajax-link';

const $nav = $('#admin-line-data').closest('#user-active-chart').find('.js-nav');
const url = $nav.find('li.active').data('url');
const $adminLine = $('#admin-line-data');

let myChart = echarts.init($adminLine[0]);
let options ={
  tooltip: {
    trigger: 'axis',
    axisPointer: {
      type: 'shadow',
      crossStyle: {
        color: '#999'
      }
    }
  },
  legend: {
    top: '10px',
    data:[Translator.trans('admin.data_center.chart.login_num'),Translator.trans('admin.data_center.chart.study_num'),Translator.trans('admin.data_center.chart.study_time')]
  },
  xAxis: [
    {
      type: 'category',
      data: [],
      splitLine: {
        show:false
      },
      axisPointer: {
        type: 'shadow'
      }
    }
  ],
  yAxis: [
    {
      type: 'value',
      name: Translator.trans('admin.data_center.chart.memberNum.tip'),
      minInterval : 1
    },
    {
      type: 'value',
      name: Translator.trans('admin.data_center.chart.hour.tip'),
      minInterval : 1
    }
  ],
  series: [
    {
      name:Translator.trans('admin.data_center.chart.login_num'),
      type:'bar',
      barGap: '-100%',
      itemStyle: {
        normal: {
          color: '#9BB8CC'
        }
      },
      data:[]
    },
    {
      name:Translator.trans('admin.data_center.chart.study_num'),
      type:'bar',
      itemStyle: {
        normal: {
          color: '#0093FF'
        }
      },
      data:[]
    },
    {
      name:Translator.trans('admin.data_center.chart.study_time'),
      type:'line',
      yAxisIndex: 1,
      itemStyle: {
        normal: {
          color: '#FF518B',
          width: 1
        }
      },
      data:[]
    }
  ]
};

myChart.setOption(options);
function getData(url) {
  $.get(url)
    .done(function (data) {
      myChart.showLoading();
      myChart.setOption({
        xAxis: [
          {
            data: data.xAxis.data,
          }
        ],
        series: [
          {
            data:data.series.dataLoginNum,
          },
          {
            data: data.series.dataLearnUsersNum,
          },
          {
            data: data.series.dataLearnTime,
          }
        ]
      });
      myChart.hideLoading();
    })
    .error(function (err) {
      console.error(err);
    });
}
getData(url);

window.addEventListener('resize', () => { 
  myChart.resize();  
});
$nav.on('click', 'li', function (e) {
  const $self = $(this);
  const url = $self.data('url');
  $self.siblings().removeClass('active');
  $self.addClass('active');
  getData(url);
});

const $searchWrap = $('.js-search-wrap');

for (let i = 0; i < $searchWrap.length; i++) {
  new ajaxLink({element: $searchWrap[i]});
}

$('.js-tab-sec').on('mouseover', '.es-icon-more', function () {
  $(this).popover('show');
}).on('mouseleave', '.es-icon-more', function () {
  $(this).popover('destroy');
});