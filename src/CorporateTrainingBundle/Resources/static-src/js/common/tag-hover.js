class Tag {
  constructor(props) {
    this.options = {
      el: '',
      tag: '.js-type-tag'
    };

    Object.assign(this.options, props);
    this.init();
  }

  init() {
    this.hover();
  }

  hover() {
    let typeClass = '';
    $(this.options.el).on('mouseover', this.options.tag, function() {
      typeClass = $(this).data('type');
      $(this).removeClass('tag').addClass(typeClass);
    }).on('mouseleave', this.options.tag, function() {
      typeClass = $(this).data('type');
      $(this).addClass('tag').removeClass(typeClass);
    });
  }
}

export default Tag;