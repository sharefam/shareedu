export default class FullCalendar {
  constructor(options) {
    this.options = this.getOptions(options);
    this.init();
  }

  getOptions({selector = null}) {
    return { selector };
  }

  init() {
    this.elem = $(this).fullCalendar(this.options);
  }
}