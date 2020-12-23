let barOptions = {
  color: ['#1AC08C'],
  tooltip: {
    trigger: 'axis',
    axisPointer: {
      type: 'shadow'
    },
  },
  grid: {
    left: '3%',
    right: '4%',
    bottom: '10%',
    containLabel: true
  },
  xAxis: {
    type: 'category',
    data: [],
    splitLine: {
      show: false
    },
    show: true,
    axisLabel: {
      formatter: function(value) {
        return value && value.length > 5 ? value.substring(0, 5) + '..' : value;
      }
    }
  },
  yAxis: {
    type: 'value',
    minInterval: 1
  },
  series: {
    name: Translator.trans('admin.data_center.survey_chart.name'),
    data: [],
    barGap: '30%',
    type: 'bar'
  }
};

let pieOptions = {
  title: {
    text: '',
    x: 'center',
  },
  tooltip: {
    trigger: 'item',
    formatter: function (params) {
      let tips;
      let datas = params.data;
      if (name.length > 10) {
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
    orient: 'vertical',
    right: '10',
    y: 'center',
    data: [],
    formatter: function (name) {
      return name.length > 10 ? (name.substring(0, 10) + '...') : name;
    }
  },
  series: [{
    name: Translator.trans('admin.data_center.chart.teacher_distribute'),
    type: 'pie',
    radius: '50%',
    center: ['45%', '50%'],
    data: [],
  }],
};

const axisLabel = {interval: 0,rotate: 40};
const center = ['50%', '50%'];
const barWidth = '50';
export {
  barOptions,
  pieOptions,
  axisLabel,
  center,
  barWidth
};