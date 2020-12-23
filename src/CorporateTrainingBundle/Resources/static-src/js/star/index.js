let score = Number($('.icon-star-group').attr('data'));
star(score);
function star(score){
  if(score > 5){
    return;
  }
  if(isInteger(score)){
    changeStar();
  }else{
    changeStar();
    $('.survey-score-star').eq(Math.floor(score)).removeClass('es-icon-staroutline').addClass('es-icon-starhalf');
  }
}
function isInteger(obj) {
  return Math.floor(obj) === obj;
}
function changeStar(){
  $('.survey-score-star').each(function(index,item){
    if(index+1 > score){
      $('.survey-score-star').eq(index).addClass('es-icon-staroutline');
    }else{
      $('.survey-score-star').eq(index).addClass('es-icon-star');
    }
  });
}
let flag = $('.user-avatar-container').children().hasClass('actions');
if(flag){
  $('.user-avatar-container .name').css({
    marginTop: '-20px'
  });
}

$('.blurr-bg').css({'-webkit-filter': 'blur(100px)'});