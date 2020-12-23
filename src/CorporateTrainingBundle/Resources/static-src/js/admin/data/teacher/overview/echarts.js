import FetchData from './base';
const $nav = $('#admin-teacher-survey-data').closest('#teacher-survey-chart').find('.js-nav');
let surveyUrl = $nav.find('li.active').data('url')+'&year='+$('#year').val();
let fetchData = new FetchData();
let countID = '#survey-teacher-count';

// init echarts
fetchData.getSurveyData(surveyUrl, 'bar', 'admin-teacher-survey-data', countID);
fetchData.getProfessionFieldData('bar', 'admin-teacher-profession-field-data');
fetchData.getLevelData('pile', 'admin-teacher-level-data');

// select change echarts
$('select[name="year"]').change(function () {
  let type = $('#admin-teacher-survey-data').closest('#teacher-survey-chart').find('.bar-or-pie').find('li.active').data('type');
  surveyUrl = $nav.find('li.active').data('url')+'&year='+$('#year').val();
  fetchData.getSurveyData(surveyUrl, type, 'admin-teacher-survey-data', countID);
});

//click change echarts
$nav.on('click', 'li', function (e) {
  const $self = $(this);
  $self.siblings().removeClass('active');
  $self.addClass('active');
  surveyUrl = $nav.find('li.active').data('url')+'&year='+$('#year').val();
  let type = $('#admin-teacher-survey-data').closest('#teacher-survey-chart').find('.bar-or-pie').find('li.active').data('type');
  fetchData.getSurveyData(surveyUrl, type, 'admin-teacher-survey-data', countID);
});

$('.bar-or-pie').on('click', 'li', function (e) {
  const $self = $(this);
  $self.siblings().removeClass('active');
  $self.addClass('active');
  let navType = $(this).parents('.bar-or-pie').data('type');
  let chartType = $(this).data('type');
  let url;
  switch (navType) {
  case 'survey':
    url = $nav.find('li.active').data('url')+'&year='+$('#year').val();

    fetchData.getSurveyData(url, chartType, 'admin-teacher-survey-data', countID);
    break;
  case 'profession-field':
    fetchData.getProfessionFieldData(chartType, 'admin-teacher-profession-field-data');
    break;
  case 'level':
    fetchData.getLevelData(chartType, 'admin-teacher-level-data');
    break;
  }
});
