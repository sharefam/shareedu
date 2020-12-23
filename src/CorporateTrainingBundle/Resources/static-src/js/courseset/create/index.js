import Create from './create';

new Create($('#courseset-create-form'));

if($('#user-orgCode').length>0){
  new window.$.CheckTreeviewInput({
    $elem: $('#user-orgCode'),
    selectType: 'single',
  });
}
