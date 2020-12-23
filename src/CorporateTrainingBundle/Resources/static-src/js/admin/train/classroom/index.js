$('.course-table__row').on('mouseover',function(){
  $(this).find('.course-set__num').hide();
  $(this).find('.course-set-tip').show();
}).on('mouseout',function(){
  $(this).find('.course-set__num').show();
  $(this).find('.course-set-tip').hide();
});