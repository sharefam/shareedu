export default class Load {
  constructor(percent = 'loading...', opts = {}) {
    this.opts = opts;
    this.percent = percent;
    this.init();
  }

  template({ extend = '' }, percent) {
    return `<div class="ch-loading-wrap">
    <p class="loading-txt">${percent}</p>
    <div class="shcl">
        <div style="left: 39px; top: 0px; "></div>
        <div style="left: 59px; top: 5px; animation-delay: 0.0833333s;"></div>
        <div style="left: 73px; top: 19px; animation-delay: 0.166667s;"></div>
        <div style="left: 78px; top: 39px; animation-delay: 0.25s;"></div>
        <div style="left: 73px; top: 58px; animation-delay: 0.333333s;"></div>
        <div style="left: 59px; top: 73px; animation-delay: 0.416667s; "></div>
        <div style="left: 39px; top: 78px; animation-delay: 0.5s; "></div>
        <div style="left: 20px; top: 73px; animation-delay: 0.583333s;"></div>
        <div style="left: 5px; top: 59px; animation-delay: 0.666667s;"></div>
        <div style="left: 0px; top: 39px; animation-delay: 0.75s;"></div>
        <div style="left: 5px; top: 19px; animation-delay: 0.833333s; "></div>
        <div style="left: 19px; top: 5px; animation-delay: 0.916667s;"></div>
    </div>
    ${extend}
   </div>`;
  }

  init() {
    this.$elem = $(this.template(this.opts, this.percent));
    this.onUpdate = this.opts.onUpdate || function() {};
    this.$txt = this.$elem.find('.loading-txt');
  }

  show(extent) {
    this.$elem.appendTo($('body'));
  }

  update() {
    this.onUpdate || this.onUpdate.call(this);
  }

  remove() {
    this.$elem.remove();
  }

  getPercent() {
    return this.$txt.html(); 
  }
  
  setPercent( txt = 0) {
    this.$txt.html(`${txt}%`);
  }
}
