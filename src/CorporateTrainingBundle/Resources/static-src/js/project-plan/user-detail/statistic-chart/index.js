import '../../../common/echarts-theme';


let courseRate = $.trim($('#course-rate').data('online-course-completion-rate'));
let attendanceRate = $.trim($('#attendance-rate').data('finished-attend-completion-rate'));
let passingRate = $.trim($('#passing-rate').data('passed-homework-completion-rate'));
let examRate = $.trim($('#exam-rate').data('pass-exam-completion-rate'));
let offlineExamRate = $.trim($('#offline-exam-rate').data('pass-offline-exam-completion-rate'));

let courseRateChart = echarts.init(document.getElementById('course-rate'), 'corporateTraining');
let attendanceRateChart = echarts.init(document.getElementById('attendance-rate'), 'corporateTraining');
let passingRateChart = echarts.init(document.getElementById('passing-rate'), 'corporateTraining');
let examRateChart = echarts.init(document.getElementById('exam-rate'), 'corporateTraining');
let offlineExamRateChart = echarts.init(document.getElementById('offline-exam-rate'), 'corporateTraining');

function getOption({title = '', data = []}) {
  return  {
    title: {
      text: title,
      x: 'center',
      bottom: 0,
      textStyle: {
        color: '#313131',
        fontSize: 14,
        fontWeight: 'normal'
      }
    },
    tooltip: {
      trigger: 'item',
      formatter: '{b} ： {d}%'
    },
    series: [{
      type: 'pie',
      radius: '66%',
      center: ['50%', '45%'],
      data: data
    }],
    color: ['#1AC08C','#F0354B']
 
  };
}

$('[data-toggle=\'popover\']').popover();

courseRateChart.setOption(getOption({
  title: Translator.trans('project_plan.user_detail.course_rate'),
  data:[
    { value: courseRate, name: Translator.trans('project_plan.user_detail.finished') },
    { value: 100 - courseRate, name: Translator.trans('project_plan.user_detail.unfinished') }
  ]
}));

attendanceRateChart.setOption(getOption({
  title: Translator.trans('project_plan.user_detail.attendance_rate'),
  data:[
    { value: attendanceRate, name: Translator.trans('project_plan.user_detail.attendance') },
    { value: 100 - attendanceRate, name: Translator.trans('project_plan.user_detail.no_attendance') }
  ]
}));

passingRateChart.setOption(getOption({
  title: Translator.trans('project_plan.user_detail.passing_rate'),
  data:[
    { value: passingRate, name: Translator.trans('project_plan.user_detail.pass') },
    { value: 100 - passingRate, name: Translator.trans('project_plan.user_detail.unpass') }
  ]
}));


examRateChart.setOption(getOption({
  title: Translator.trans('project_plan.user_detail.exam_passing_rate'),
  data:[
    { value: examRate, name: Translator.trans('project_plan.user_detail.pass') },
    { value: 100 - examRate, name: Translator.trans('project_plan.user_detail.unpass') }
  ]
}));

offlineExamRateChart.setOption(getOption({
  title: Translator.trans('project_plan.user_detail.exam_passing_rate'),
  data:[
    { value: offlineExamRate, name: Translator.trans('project_plan.user_detail.pass') },
    { value: 100 - offlineExamRate, name: Translator.trans('project_plan.user_detail.unpass') }
  ]
}));


