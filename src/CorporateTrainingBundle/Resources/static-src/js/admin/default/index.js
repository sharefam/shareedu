import Swiper from 'swiper';
import Tag from '../../common/tag-hover';
if (document.documentElement.lang != 'en') {
  require('libs/fullcalendar/locale/zh-cn');
}

let type = $('.work-menology-tab.active').find('a').data('type');

renderCalendar(type);
renderTodayFocus(type);

$('.work-menology-tab').click((e) => {
  let $parent = $(e.target).parents('.work-menology-tab');
  $parent.addClass('active').siblings().removeClass('active');
  
  let type = $(e.target).data('type');
  renderCalendar(type);
  renderTodayFocus(type);
});

function renderTodayFocus(type) {
  let url = $('.today-focus-wrapper').data(type+'Url');
  $.get(url, (html) => {
    $('.admin-container .ct-panel-body .today-focus-wrapper').html(html);
    const len = $('.today-focus-wrapper').find('.content-course-list').length;
    if(len && len < 5) {
      const $desc = $('.today-focus-wrapper .content-body'); 
      emptyList($desc, len);
    }
    
    mySwiper();
  });
}

function renderCalendar(type) {
  $('#calendar').fullCalendar('destroy');
  let url = $('#calendar-url').data(type+'Url');
  
  $('#calendar').fullCalendar({
    eventLimit: true,
    height: 670,
    eventMouseover: function(event, jsEvent) {
      const $current = $(jsEvent.currentTarget);
      const flag = $current.hasClass('fc-start');
      let title = $current.prop('title');
      if(flag && !title) {
        title = $(this).find('.fc-title').text();
        $(this).prop('title', title);
      }
    },
    events: function(start, end, timezone, callback) {
      $.ajax({
        url: url,
        type: 'GET',
        data: {
          start: start.unix(),
          end: end.unix()
        },
        locale: document.documentElement.lang,
        success: function(data) {
          let events = [];
          $.each(data,function() {
            // let detail = $(this).attr('detail');
            let title = $(this).attr('name') ? $(this).attr('name') : $(this).attr('title');
            events.push({
              title: title,
              start: formatDateTime($(this).attr('startTime')), // will be parsed
              end: formatDateTime($(this).attr('endTime')),
              className: $(this).attr('focus_type'),
              url: $(this).attr('url')
            });
          });
          callback(events);
        }
      });
    }
  });
}

function formatDateTime(timeStamp) {
  let date = new Date();
  date.setTime(timeStamp * 1000);
  let y = date.getFullYear();
  let m = date.getMonth() + 1;
  m = m < 10 ? ('0' + m) : m;
  let d = date.getDate();
  d = d < 10 ? ('0' + d) : d;
  let h = date.getHours();
  h = h < 10 ? ('0' + h) : h;
  let minute = date.getMinutes();
  let second = date.getSeconds();
  minute = minute < 10 ? ('0' + minute) : minute;
  second = second < 10 ? ('0' + second) : second;
  return y + '-' + m + '-' + d+' '+h+':'+minute+':'+second;
}
new Tag({el: '.today-focus-wrapper'});

function emptyList($desc, len) {
  const html = template(len); 
  $desc.append(html);
}

function mySwiper() {
  const $swiper = $('.today-focus-wrapper').find('.swiper-container');
    
  $swiper.each(function(index, item) {
    if($(item).find('.swiper-slide').length > 1) {
      new Swiper(item, {
        speed: 2000,
        loop: true,
        autoplay: 5000,
      });
    }
  });
}

function template(len) {
  len = 5 - len;
  let html = '';
  for(let i = 0; i < len; i++) {
    html += '<div class="content-course-list"><img src="/assets/img/backstage/focus/placeholder.png" class="focus-placeholder-img"></div>';
  }
  return html;
}