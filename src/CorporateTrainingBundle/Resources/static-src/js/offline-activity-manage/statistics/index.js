import '../../common/echarts-theme';

class initPieChart {
   
  constructor(props) {
    Object.assign(this, props);
  }

  init() {
    let myChart = echarts.init(document.getElementById(this.id));
    myChart.setOption(this._chartConfig());
    $(window).on('resize', function() {
      myChart.resize();
    });
  }

  _traverseData() {
    let data = this.data,
      arr = [];
    for (var i in data) {
      arr.push(data[i].name);
    }
    return arr.reverse();
  }

  _chartConfig() {
    let options = {
      tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b} : {c} ({d}%)'
      },
      color: ['#e1e1e1', '#50ad55', '#fda429'],
      legend: {
        orient: 'vertical',
        right: 'right',
        top: 'center',
        itemWidth: 8,
        itemHeight: 8,
        data: this._traverseData()
      },
      series: [{
        name: '',
        type: 'pie',
        radius: '55%',
        center: ['35%', '50%'],
        labelLine: {
          normal: {
            show: false
          }
        },
        label: {
          normal: {
            show: false,
            position: 'center'
          }

        },
        data: this.data
      }]
    };
    return options;
  }
}

class initScoreLineChart {
  constructor(props) {
    Object.assign(this, props,
      this.title = props.title || ''
    );
  }
  init() {
    let myChart = echarts.init(document.getElementById(this.id), 'corporateTraining');
    myChart.setOption(this._chartConfig());
    $(window).on('resize', function() {
      myChart.resize();
    });

  }
  _traverseScoreRange() {
    let data = this.data,
      arr = [];
    for (var i in data) {
      arr.push(data[i].scoreRange);
    }
    return arr;
  }
  _traverseNum() {
    let data = this.data,
      arr = [];
    for (var i in data) {
      arr.push(data[i].num);
    }
    return arr;
  }
  _chartConfig() {
    let options = {
      tooltip: {
        trigger: 'axis'
      },
      calculable: true,
      xAxis: [{
        type: 'category',
        boundaryGap: false,
        data: this._traverseScoreRange()
      }],
      yAxis: [{
        type: 'value',
        minInterval: 1
      }],
      series: [{
        name: this.title,
        type: 'line',
        stack: '人数',
        areaStyle: {normal: {}},
        data: this._traverseNum()
      }]
    };
    return options;
  }
}

let initData = function (element, chart, title) {
  let url = $(element).data('url');
  let id = element.slice(1);
  return (
    $.get(url, function (data) {
      return (
        new chart({
          id: id,
          data: data,
          title: title
        }).init()
      );
    })
  );

};

let runPeiChart = function () {
  initData('#attend-chart', initPieChart)
    .then(initData('#pass-chart', initPieChart));
};
runPeiChart();

let runLineChart = function () {
  initData('#score-chart', initScoreLineChart, Translator.trans('offline_activity.statistics.score_distribution'));
};
runLineChart();