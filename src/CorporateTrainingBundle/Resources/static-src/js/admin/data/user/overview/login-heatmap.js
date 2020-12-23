const myChart = echarts.init(document.getElementById('login-heatmap-data'));
const $nav = $('.js-heatmap').find('.js-nav');
const url = $nav.find('.active').find('a').data('url');

let hours = ['12a', '1a', '2a', '3a', '4a', '5a', '6a',
  '7a', '8a', '9a','10a','11a',
  '12p', '1p', '2p', '3p', '4p', '5p',
  '6p', '7p', '8p', '9p', '10p', '11p'];

let days = [Translator.trans('week.format.monday'), Translator.trans('week.format.tuesday'), Translator.trans('week.format.wednesday'), Translator.trans('week.format.thursday'), Translator.trans('week.format.friday'), Translator.trans('week.format.saturday'), Translator.trans('week.format.sunday')];

let options = {
  tooltip: {
    position: 'top',
    formatter: []
  },
  animation: false,
  grid: {
    height: '78%',
    y: '15%'
  },
  xAxis: {
    type: 'category',
    data: hours,
    splitArea: {
      show: true
    }
  },
  yAxis: {
    type: 'category',
    data: days,
    splitArea: {
      show: true
    }
  },
  visualMap: {
    min: 0,
    max: [],
    calculable: true,
    orient: 'horizontal',
    right: '8%',
    top: ''
  },
  series: [{
    name: Translator.trans('admin.default.login_user_count'),
    type: 'heatmap',
    data: [],
    label: {
      normal: {
        show: true,
      }
    },
    itemStyle: {
      emphasis: {
        shadowBlur: 10,
        shadowColor: 'rgba(0, 0, 0, 0.5)'
      }
    }
  }]
};

myChart.setOption(options);

function getData(url) {
  $.ajax({
    type: 'GET',
    url: url,
    data: {
      'orgCode': $('#login-heatmap-chart').find('[name=orgCode]').val(),
      'postId' : $('#login-heatmap-chart').find('[name=postId]').val(),
      'type' : $('#login-heatmap-chart').find('.js-nav').children('.active').find('a').data('value'),
      'dataSearchTime' : $('#login-heatmap-chart').find('[name=dataSearchTime]').val()
    },
    success: function(data) {
      let $dataArray = JSON.parse(data);
      let $data =dataMap($dataArray['loginHourData']);
      myChart.showLoading();
      myChart.setOption({
        tooltip: {
          formatter: function ($data) {
            let str =
              `${$data['seriesName']}<br>
              <span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:${$data['color']}"></span> ${$data['name']} :  ${$data['data'][2]}`
            ;
            return str;
          }
        },
        visualMap: {
          max: $dataArray['maxLoginHourData']
        },
        series: [
          {
            data: $data
          }
        ],
      });
      myChart.hideLoading();
    }

  });
}
getData(url);

function dataMap(data) {
  data = data.map(function (item) {
    return [item[1], item[0], item[2] || '-'];
  });

  return data;
}
window.addEventListener('resize', () => {
  myChart.resize();
});

$nav.on('click', 'li', function (e) {
  const $self = $(this);
  const url = $self.find('a').data('url');
  $self.siblings().removeClass('active');
  $self.addClass('active');
  getData(url);
});

$('.js-login-search').click(function () {
  const url = $('.js-login-search').data('url');
  getData(url);
});
