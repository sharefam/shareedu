let myChart = echarts.init(document.getElementById('project-plan-data'));
let data = JSON.parse($('#chartData').val());

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
    data:[ Translator.trans('admin.data_center.chart.projectPlansNum'), Translator.trans('admin.data_center.chart.projectPlansMembersCount')]
  },
  xAxis: [
    {
      type: 'category',
      data: [ Translator.trans('month.format.jan'), Translator.trans('month.format.feb'), Translator.trans('month.format.mar'), Translator.trans('month.format.apr'), Translator.trans('month.format.may'), Translator.trans('month.format.jun'), Translator.trans('month.format.jul'), Translator.trans('month.format.aug'), Translator.trans('month.format.sep'), Translator.trans('month.format.oct'), Translator.trans('month.format.nov'), Translator.trans('month.format.dec')],
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
      name: Translator.trans('admin.data_center.chart.project_plan.num.tip'),
      minInterval: 1,
      splitLine: {
        show:true
      },
      axisLabel: {
        formatter: '{value}'
      }
    },
    {
      type: 'value',
      name: Translator.trans('admin.data_center.chart.project_plan.memberNum.tip'),
      minInterval: 1,
      yAxisIndex: 1,
      splitLine: {
        show:true
      },
      axisLabel: {
        formatter: '{value}'
      }
    },
  ],
  series: [
    {
      name: Translator.trans('admin.data_center.chart.projectPlansNum'),
      type:'bar',
      itemStyle: {
        normal: {
          color: '#0093FF'
        }
      },
      data: data.series.projectPlansNum
    },
    {
      name: Translator.trans('admin.data_center.chart.projectPlansMembersCount'),
      type:'bar',
      yAxisIndex: 1,
      itemStyle: {
        normal: {
          color: '#9BB8CC'
        }
      },
      data: data.series.projectPlanMembersNum,
    },
  ]
};

myChart.setOption(options);

const defaultOpts = {
  language: document.documentElement.lang,
  autoclose: true,
  format: 'yyyy',
  startView:4,
  minView:4,
  maxView:4,
};
$('[name=year]').datetimepicker(defaultOpts);

new window.$.CheckTreeviewInput({
  $elem: $('#user-orgCode'),
  selectType: 'single',
});