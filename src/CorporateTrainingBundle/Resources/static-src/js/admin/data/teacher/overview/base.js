
import  { axisLabel, center, barWidth } from './echarts-config';
import EchartsBase from './echarts-base';
let echartsBase = new EchartsBase();
class FetchData {
  constructor() {

  }
  init() {

  }
  getSurveyData(url, type, id, countID) {  
    echartsBase.init({url, type, id, barWidth, countID});
  }
  getProfessionFieldData(type, id) {
    let url = $(`#${id}`).data('url');
    echartsBase.init({url, type, id, axisLabel, center});  
  }
  getLevelData(type, id) {
    let url = $(`#${id}`).data('url');
    echartsBase.init({url, type, id, axisLabel});  
  }
}

export default FetchData;