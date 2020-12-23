import Importer from '../../widget/importer';

new Importer({
  rules: {}
});
let $notificationSetting = $('#importNotificationSetting');
let $form = $('#importer-form');
$notificationSetting.prop('checked', true);

$notificationSetting.change(function (e) {
  if($notificationSetting.is(':checked') === true){
    $form.find('#notificationSettingInput').val(1);
  }else{
    $form.find('#notificationSettingInput').val(0);
  }
  console.log($form.find('#notificationSettingInput').val());
});
