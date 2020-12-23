import '../../../../common/echarts-theme';
import { pieOptions, levelConfig } from './echarts-config';
const id = 'admin-data-center-course-online-data';

class Echart {
  static getMaxCode(data) {
    let temp = [];

    data.forEach((item) => {
      temp.push(item.level.code);
    });

    return Math.max.apply(null, temp);
  }

  constructor(props) {
    const { id } = props;

    let data = this.getData(id);

    if(data.names.length > 0){
      this.chart = echarts.init(document.getElementById(id), 'corporateTraining');
      this.level(data.data);
      this.pie(this.chart, data.data);
    } else {
      $(document.getElementById(id)).html('<div class="empty">'+Translator.trans('admin.data_center.chart.course_num_overview.empty_tips')+'</div>');
    }
  }

  pie(chart, data) {
    this.resize(chart);
    this.processData(data);

    chart.setOption(pieOptions);
    chart.setOption({
      series: data
    });
  }

  level(data) {
    if (!Array.isArray(data)) {
      throw new Error('the data must be Array');
    }

    const len = data.length;
    const max = Echart.getMaxCode(data);

    let code = Number;
    let obj = {};

    for (let i = 0; i < len; i++) {
      code = data[i].level.code - 1;
      obj = levelConfig[code];
      data[i] = { ...data[i], ...obj };
      this.setRadius(data[i], i, max);
    }
  }

  setRadius(item, index, max) {
    const base = 30;
    const insideRadius = `${((3 * index)/max) * base}%`;

    if (max == 1) {
      item.radius = [insideRadius, `${(0.5 + max) * 30}%`];
      return;
    }

    if (max == 2) {
      item.radius = [insideRadius, `${(1 + index) * 30}%`];
      return;
    }

    item.radius = [insideRadius, `${(0.5 + index) * 30}%`];
  }

  resize(chart) {
    window.addEventListener('resize', () => {
      chart.resize();
    });
  }

  getData(id) {
    let data = [];
    let url = $('#' + id).data('url');
    let params = $('#online-course-data-search').serialize();

    $.ajax({
      async: false,
      url: url,
      data: params,
      success: function(result) {
        data = result;
      }
    });

    return data;
  }

  processData(data) {
    const value = 0;

    for (let i = 0; i < data.length; i++) {
      for (let j = 0; j < data[i].data.length; j++) {
        if (data[i].data[j].value == undefined) {
          data[i].data[j].value = value;
        }
      }
    }
  }
}

new Echart({ id });