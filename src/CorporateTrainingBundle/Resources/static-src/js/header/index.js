class Language {
  constructor(opts){
    this.state = $(opts.state).data('lan');
    this.$el = $(opts.el);
    this.$loadingDom = $('.js-qcode-hover').find('.js-inform-loading');
    this.init();

  }

  init(){
    let self = this;
    this.state_lan(this.state);
    $('.js-qcode-hover').hover(function (e) {
      if(self.$loadingDom.find('.js-qcode-image').length === 0){
        let loading = cd.loading();
        self.$loadingDom.html(loading);
        $.ajax({
          type: 'post',
          url: $('.js-qcode-hover').data('url'),
          dataType: 'json',
          success: function (data) {
            let $html = '<img class="qcode js-qcode-image" src="" alt="">';
            self.$loadingDom.html($html);
            self.$loadingDom.find('.js-qcode-image').attr('src', data.img);
          }
        });
      }
    });
  }

  state_lan(state){
    switch(state){
    case 'en':
      this.show_lan('English');
      break;
    case 'zh_CN':
      this.show_lan('中文');
      break;
    default:
      this.show_lan('English');
    }      
  }

  show_lan(html){
    this.$el.html(html);
  }
  
}

new Language({
  state:'.more-lan',
  el:'.show_lan'
});

