import CountUp from 'countup.js';
class Count {
  constructor(props) {
    this.options = {
      num: '.js-group-num',
      search: '.js-user-learn-search',
      serialize: '.js-form-search'
    };
    Object.assign(this.options, props);
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    const serialize = $(this.options.serialize).serialize();

    this.realData(serialize);
    this.search();
  }
  elRun(serialize) {
    let self = this;
    let token = $('meta[name=csrf-token]').attr('content');

    $(this.options.num).each(function(index, item){
      let url = $(item).data(url).url;
      if ($(item).hasClass('js-hotKeyWord')) {
        $.ajax({
          url: url,
          type: 'POST',
          data: serialize,
          success: function (data) {
            $('.user-group-tag_list').html(data);
            $('[data-toggle="tooltip"]').tooltip();
          },
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-Token', token);
          }
        });
      } else {
        let decimals = $(item).hasClass('js-group-num_total') ? 0 : 1;
        self.fetchData(url, serialize, token).then(res => {
          let numAnim = self.roll({targetElem: $(item)[0], endVal: isNaN(res.data) ? 0 : res.data, decimals});

          self.detection(numAnim);
        });
      }
    });
  }
  
  search() {
    $(this.options.search).on('click', () =>{
      const serialize = $(this.options.serialize).serialize();

      this.realData(serialize);
    });
  }

  realData(serialize) {
    this.elRun(serialize);
  }

  detection(numAnim, url) {
    let self = this;
    if (!numAnim.error) {
      numAnim.start(function() {
      });
    } else {
      console.error(numAnim.error);
    }
  }
  /**
   *
   *
   * @param {*} targetElem id of html element, input, svg text element, or var of previously selected element/input where counting occurs
   * @param {*} startVal   the value you want to begin at
   * @param {*} endVal     the value you want to arrive at
   * @param {*} decimals   (optional) number of decimal places in number, default 0 
   * @param {*} duration   (optional) duration in seconds, default 2 
   */
  roll({targetElem, startVal = 0, endVal = 99.99, decimals = 1, duration = 1}) {
    return new CountUp(targetElem, startVal, endVal,decimals, duration);
  }

  pauseResume() {
   
  }

  reset() {

  }

  update(endVal) {
   
  }
 
  /**
   *
   *
   * @param {*} url
   * @returns 
   */
  fetchData(url, data, token) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: url,
        type: 'POST',
        data,
        success: function (data) {
          resolve(data);
        },
        error: function () {
          console.log('error');
        },
        beforeSend: function(xhr) {
          xhr.setRequestHeader('X-CSRF-Token', token);
        }
      });
    });
  }
}

export default Count;