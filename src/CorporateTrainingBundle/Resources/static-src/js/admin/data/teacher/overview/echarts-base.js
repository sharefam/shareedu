import '../../../../common/echarts-theme';
import { barOptions, pieOptions } from './echarts-config';
export default class EchartsBase {
  constructor() {
    
  }
  init(props) {
    let {url, type, id, axisLabel, center, barWidth, countID} = props;
    this.changeData(url, type, id, axisLabel, center, barWidth, countID);
  }
  changeData(url, type, id, axisLabel, center, barWidth, countID) {
    let chart = echarts.init(document.getElementById(id));
    this.resize(chart);
    chart.showLoading();
    this.fetchData(url).then(res => {
      if (type == 'bar') {
        this.bar(chart, res, axisLabel, barWidth);
      } else {
        this.pie(chart, res, id, center);
      }
      if(countID) {
        $(countID).html(res.count);
      }
      chart.hideLoading();
    });
  }
  bar(chart, data, axisLabel, barWidth) {
    chart.setOption(barOptions);
    chart.setOption({
      tooltip: {
        formatter: function (datas) {

          let res = Translator.trans('my.teaching_record.chart.survey') + datas[0].name + '<br/>',
            val;
          for (let i = 0, length = datas.length; i < length; i++) {
            val = (datas[i].value)?datas[i].value:0;
            res += datas[i].seriesName + '：' + val + '<br/>';
          }
          return res;
        }
      },
      xAxis: {
        data: data.names || data.levelNames || data.professionFieldNames,
        axisLabel: axisLabel ? axisLabel : {interval: 'auto',rotate: 0}
      },
      series: {
        data: data.data,
        barWidth: barWidth ? barWidth : ''
      }
    });
  }
  pie(chart, data, id, center) {
    chart = echarts.init(document.getElementById(id), 'corporateTraining');
    this.resize(chart);
    chart.setOption(pieOptions);
    chart.setOption({
      legend: {
        // data: data.names || data.levelNames,
      },
      series: [{
        data: data.pieData,
        center: center ? center : ['50%', '50%']
      }],
    });
  }
  fetchData(url) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: url,
        type: 'get',
        success: function (data) {
          resolve(data);
        },
        error: function () {
          console.log('error');
        }
      });
    });
  }
  resize(chart) {
    window.addEventListener('resize', () => { 
      chart.resize();  
    });
  }
}