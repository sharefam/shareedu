import notify  from 'common/notify';

if (document.documentElement.lang != 'en') {
  require('libs/fullcalendar/locale/zh-cn');
}

$('#calendar').fullCalendar({
  events: function(start, end, timezone, callback) {
    $.ajax({
      url: $('#projectPlanItems').data('url'),
      type: 'POST',
      data: {
        // our hypothetical feed requires UNIX timestamps
        start: start.unix(),
        end: end.unix()
      },
      locale: document.documentElement.lang,
      success: function(data) {
        var events = [];
        $.each(data,function() {
          var detail = $(this).attr('detail');
          var title = detail.name ? detail.name : detail.title;
          events.push({
            title: title,
            start: formatDateTime($(this).attr('startTime')), // will be parsed
            end: formatDateTime($(this).attr('endTime')),
            url: $(this).attr('url')
          });
        });
        callback(events);
      }
    });
  }
});

$('.projectplan-operate').click(function () {

  let notifyTitle = $(this).data('notifyTitle');
  if (!confirm(`${Translator.trans('project_plan.secondary_confirmation')}${notifyTitle}`)) {
    return;
  }

  $.post($(this).data('url'), function(result){
    console.log(result);
    if (result) {
      notify('success', `${notifyTitle}${Translator.trans('project_plan.success')}!`);
      window.location.reload();
    } else {
      notify('danger', `${notifyTitle}${Translator.trans('project_plan.error')}!`);
    }
  }).error(function() {
    notify('danger', `${notifyTitle}${Translator.trans('project_plan.error')}!`);
  });
});

function formatDateTime(timeStamp) {
  var date = new Date();
  date.setTime(timeStamp * 1000);
  var y = date.getFullYear();
  var m = date.getMonth() + 1;
  m = m < 10 ? ('0' + m) : m;
  var d = date.getDate();
  d = d < 10 ? ('0' + d) : d;
  var h = date.getHours();
  h = h < 10 ? ('0' + h) : h;
  var minute = date.getMinutes();
  var second = date.getSeconds();
  minute = minute < 10 ? ('0' + minute) : minute;
  second = second < 10 ? ('0' + second) : second;
  return y + '-' + m + '-' + d+' '+h+':'+minute+':'+second;
}
