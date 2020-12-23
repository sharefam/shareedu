import Base from './base';
new Base();

if($('#user-orgCode').length>0){
  new window.$.CheckTreeviewInput({
    $elem: $('#user-orgCode'),
    selectType: 'single',
  });
}