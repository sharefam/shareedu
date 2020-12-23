import OverviewDateRangePicker from '../../../../common/date-range-picker';
import './el-table';
new OverviewDateRangePicker('#date-range-picker');

$('.js-date-range-empty').val('');

new window.$.CheckTreeviewInput({
  $elem: $('#user-orgCode'),
  selectType: 'single',
});