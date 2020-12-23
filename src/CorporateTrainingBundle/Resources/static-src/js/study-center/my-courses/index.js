class joinCourse {
  constructor(){
    this.flag = false;
    this.$tabElem = $('.course-content');
    this.$filterElem = $('.course-filtrate');
    echo.init();
    this.init();
  }
  init(){
    this.initEvent();
  }
  initEvent(){
    this.filterEvent(this.$filterElem);
    this.tabEvent(this.$tabElem);
  }
  tabEvent($tabElem){
    $tabElem.on('click','li',(e)=>{
      $(e.currentTarget).siblings().removeClass('active');
      $(e.currentTarget).addClass('active');
    });
  }
  filterEvent($filterElem){
    let _self = this;
    let $icon = $filterElem.find('.es-icon');
    $filterElem.on('click',()=>{
      $('.courses-tabs').slideToggle();
      _self.flag = !_self.flag;
      if(_self.flag){
        $icon.removeClass('es-icon-keyboardarrowdown').addClass('es-icon-keyboardarrowup');
      }else{
        $icon.removeClass('es-icon-keyboardarrowup').addClass('es-icon-keyboardarrowdown');
      }
    });
  }
}
new joinCourse();