/**
 * [getScreenW description]
 * @return {[type]} [screen's width]
 */
export function getScreenW() {
  if (!window) {
    throw new Error('this is not borwser');
  }
  
  return window.innerWidth || document.body.clientWidth;
}

export function getTypeof(param) {
  var type = typeof param;
  if (type != "object") {
    return type;
  }
  var str = Object.prototype.toString.call(param).toLocaleLowerCase();
  return str.slice(8, str.length - 1);
}

/**
 * [simpleAdapter only support single layout]
 * @param  {[json]} data  [description]
 * @param  {[json]} match [description]
 * @return {[json]}       [description]
 */

export function simpleAdapter(data, match) {
  let ret = data;
  const keys = Object.keys(match);
  
  if (getTypeof(data) == 'array') {
    ret = data.map(function(item, index) {
      let t = keys.reduce(function(accumulator, currentValue){
        accumulator[match[currentValue]] = item[currentValue]; 
        return accumulator;
      }, {});
      return t;
    });
  }
  return ret;
}