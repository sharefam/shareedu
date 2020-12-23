import './echarts';

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