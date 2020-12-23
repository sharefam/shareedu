import CourseOverviewDateRangePicker from './date-range-picker';

let dateRangePicker = new CourseOverviewDateRangePicker('.js-learn-data');

$('.js-data-popover').popover({
  html: true,
  trigger: 'hover',
  placement: 'top',
  template: '<div class="popover tata-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
  content: function() {
    return $(this).siblings('.popover-content').html();
  }
});
