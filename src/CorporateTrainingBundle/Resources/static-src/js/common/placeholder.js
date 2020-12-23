let typeObj = {
  div: {
    focus: function(e) {
      let val = this.$elem.html();
      val = $.trim(val.replace(/<div>|<\/div>|<br>/ig, ''));      

      if (this.preText == val) {
        this.$elem.html('');
      }
    },
    blur: function(e) {
      let val = this.$elem.html();
      val = val.replace(/<div>|<\/div>|<br>|\s/ig, '');

      if (val == '') {
        this.$elem.html(this.text);
      }
    }
  },
  input: {
    focus: function(e) {
      let val = $.trim(this.$elem.val());
      if (val.indexOf(this.preText) == 0) {
        this.$elem.val('');
      }
    },
    blur: function(e) {
      let val = $.trim(this.$elem.val());
      if (val == '') {
        this.$elem.val(this.text);
      }
    }
  }
};

export default class Placeholder {
  constructor(selector, opts) {
    this.$elem = $(selector);
    this.opts = opts;
    this.preText = this.opts.preText;
    this.type = this.opts.type;
    this.Objmethods = typeObj[this.type];
    this.text = this.opts.text;
    this.init();
  }

  init() {
    this.initEvent();
  } 

  initEvent() {
    this.$elem.on('focus', (e) => {
      this.Objmethods['focus'].call(this, e);
    });

    this.$elem.on('blur', (e) => {
      this.Objmethods['blur'].call(this);
 
    });
  }
}