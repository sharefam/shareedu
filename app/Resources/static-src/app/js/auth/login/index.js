let $form = $('#login-form');
login_position();
$(window).resize(()=>{
  login_position();
});
let validator = $form.validate({
  rules: {
    _username: {
      required: true,
    },
    _password: {
      required: true,
    }
  }
});
$('#login-form').keypress(function (e) {
  if (e.which == 13) {
    $('.js-btn-login').trigger('click');
    e.preventDefault(); // Stops enter from creating a new line
  }
});

$('.js-btn-login').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loadding');
    $form.submit();
  }
});
$('.receive-modal').click();
function login_position(){
  const space_height = 20;
  let screen_width = $(document).width();
  let $loginContainer = $('#content-container .login-container');
  let $loginVisitors = $('#content-container .latest-login-visitors');
  let position_code = $loginContainer.data('code');
  let coefficient = 300/1920;
  let space_width = coefficient * screen_width;
  let offsetTop = $loginVisitors.offset().top;
  let height = $loginVisitors[0].clientHeight;
  let top = offsetTop + height + space_height + 'px';
  const common_css = {position:'absolute', top: '50%'};
  if(screen_width < 800){
    $loginContainer.css({marginTop:'8%'});
    return;
  }
  $('.index_bg').css({minHeight:top});
  switch(position_code){
  case 0:
    $loginContainer.css({...common_css,...{left:space_width, transform:'translateY(-50%)'}});
    break;
  case 1:
    $loginContainer.css({...common_css,...{left:'50%',transform:'translate(-50%, -50%)'}});
    break;
  case 2:
    $loginContainer.css({...common_css,...{right:space_width, transform:'translateY(-50%)'}});
    break;
  }
}