import DateRangePicker from 'app/common/daterangepicker';
import Emitter from 'component-emitter';
import 'moment';

export default class OverviewDateRangePicker extends Emitter {

  constructor(containerSelector) {

    super();

    let dateRangePickerSelector = containerSelector + ' .js-date-range-input';

    new DateRangePicker(dateRangePickerSelector);

    let self = this;

    this.$drp = $(dateRangePickerSelector);

    this.$drp.on('apply.daterangepicker', function () {
      $(this).closest('#date-range-picker').find('.js-quick-day-pick').removeClass('gray-darker');
      self.emit('date-picked', {startDate:self.getStartDate(), endDate:self.getEndDate()});
    });


    let quickDayPickerSelector = containerSelector + ' .js-quick-day-pick';
    $(quickDayPickerSelector).on('click', function () {
      $(this).addClass('gray-darker').siblings().removeClass('gray-darker');
      let days = $(this).data('days');
      let now = new Date();
      self.$drp.data('daterangepicker').setEndDate(now);

      now.setDate(now.getDate() - days + 1);
      self.$drp.data('daterangepicker').setStartDate(now);
      self.emit('date-picked', {startDate:self.getStartDate(), endDate:self.getEndDate()});
    });

    const datePicker = '.js-range-picker';
    $(datePicker).on('mouseover', function() {
      $(this).find('.js-date-remove').removeClass('hidden').find('.js-date-down').addClass('hidden');
    }).on('mouseleave', function(){
      $(this).find('.js-date-remove').addClass('hidden').find('.js-date-down').removeClass('hidden');
    });

    $('.js-date-remove').click(function() {
      $(this).next('.js-date-range-input').val('');
    });

  }

  getStartDate() {
    console.log(this.$drp.data('daterangepicker').startDate);
    return this.$drp.data('daterangepicker').startDate.format('YYYY-MM-DD');
  }

  getEndDate() {
    return this.$drp.data('daterangepicker').endDate.format('YYYY-MM-DD');
  }

}