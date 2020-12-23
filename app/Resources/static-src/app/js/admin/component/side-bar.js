class Sidebar {
  constructor(props) {
    
    this.options = {
      el:'.cd-sidebar-container',
      ctTab: '.navbar-nav_middle',
      crumbs:'.ct-crumbs',
      data: [],
      output:'',
      code: null
    };
    this.options.code = $(this.options.el).data('code');
    Object.assign(this.options, props);

    this.init();
  }

  init() {
    this.setHeight();
    this.addArrowIcon();
    this.initActive();
    this.events();
  }
  events() {
    $(this.options.el).on('click','.cd-group-item_link',(event)=>{this.clickEvent(event);});
    $(this.options.ctTab).on('click',(event)=>{this.ctTab(event);});
  }
  setHeight() {
    $('html').css({height:'100%'});
    $('body').css({height:'100%'});
  }
  addArrowIcon(){
    this.buildTree();
    $(this.options.el).find('a').each((index,item)=>{
      let left = $(item).data('grade') * 16 + 40;
      $(item).css({paddingLeft:`${left}px`});
      if($(item).next('ul').length){
        $(item).append('<span class="cd-icon cd-icon-arrow-down"></span>');
      }
    });
  }
  initActive(){
    let $links= $('.cd-group-item_link');
    let href = $(this.options.ctTab + ' .active').find('a').data('href');
    let fetchData = this.options.data;
    let name = fetchData && fetchData.name;
    $links.each((index, item)=> {
      if($(item).next('ul').length){
        $(item).prop('href','javascript:;');
      }
      if($(item).hasClass('active')){
        $(item).parents('ul').show();
        $(item).parents('.cd-group-item').children('.cd-group-item_link').children('.cd-icon').removeClass('cd-icon-arrow-down').addClass('cd-icon-arrow-up');
        if(!$(item).parent('.cd-group-item_outer').length) {
          let title = $(item).parents('.cd-group-item_outer').children('a').attr('title');
          $(this.options.crumbs).text(`${name} / ${title}`);
        } else {
          $(this.options.crumbs).text(`${name}`);
        }
      }
    });
  }
  buildTree(){
    let fetchData = this.options.data;
    let data = fetchData && fetchData.children;
    let name = fetchData && fetchData.name;
    let html = '';
    if(!data)return;
    
    Object.values(data).forEach((key, i) => {
      this.options.output = '';
      html += `<li class="cd-group-item cd-group-item_outer"><a class='cd-group-item_link ${key.code == this.options.code ? 'active': ''}'  title=${key.name} data-grade=${key.grade} href=${key.url ? key.url : 'javascript:;'}  data-href=${key.url} ><span class='item-txt'>${key.name}</span></a>`;
      html +=  this.creatElement(key);
      html += '</li>';
    });
    $('.sidebar-title h5').html(name);
    $(this.options.el)[0].innerHTML = html;
  }
  creatElement(item){
    if(item.children && item.children.length){
      this.options.output += '<ul>';  
      for(let list of item.children){
        this.options.output += `<li class='cd-group-item'><a class='cd-group-item_link ${list.code === this.options.code ? 'active': ''}'  title=${list.name}  data-grade=${list.grade} href=${list.url ? list.url : 'javascript:;'} data-href=${list.url}><span class='item-txt'>${list.name}</span></a>`;
        this.creatElement(list);
      }  
      this.options.output += '</ul>';
    }
    return this.options.output;
  }
  clickEvent(event) {
    let $that = $(event.currentTarget);
    let hide = $that.next('ul').is(':hidden');
    let $parentSiblings = $that.parent().siblings();
    let $icon = $that.children('.cd-icon');
    if(hide){
      $icon.css({transform:'rotate(180deg)'});
    }else {
      if ($icon.hasClass('cd-icon-arrow-up')) {
        $icon.removeClass('cd-icon-arrow-up').addClass('cd-icon-arrow-down');
      } else {
        $icon.css({transform:'rotate(0deg)'});
      }
    }
    $parentSiblings.find('ul').stop().slideUp();
    if ($parentSiblings.find('.cd-icon').hasClass('cd-icon-arrow-up')) {
      $parentSiblings.find('.cd-icon').removeClass('cd-icon-arrow-up').addClass('cd-icon-arrow-down');
    } else {
      $parentSiblings.find('.cd-icon').css({transform:'rotate(0deg)'});
    }
    if(!$that.next('ul').length){
      $('.cd-group-item_link').removeClass('active');
      $that.addClass('active');
    }
    $that.next('ul').stop().slideToggle();
  }
  ctTab(event){
    if(event.target.nodeName.toLocaleLowerCase() === 'a' ){
      let $self = $(event.target).parent();
      $self.siblings().removeClass('active');
      $self.addClass('active');
    }
  }
}

export default Sidebar;