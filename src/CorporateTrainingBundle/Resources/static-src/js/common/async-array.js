export default class AsyncArray {
  
  constructor() {
    this.isRunning = false;
    this.array = [];
    this.autorun();

  }

  push(fn) {
    if (typeof fn != 'function') {
      throw new Error('the typeof param must be function');
    }

    this.array.push(this.wrapFn(fn));
    this.autorun();
  }

  wrapFn(fn) {
    return () => {
      fn(() => {
        this.isRunning = false; 
        this.autorun();
      });
    };
  }

  autorun() {
    if (this.isRunning) {
      return;
    } 

    if (this.array.length) {
      this.isRunning = true;
      this.currentFn = this.array.shift();
      this.currentFn();
    } else {
      this.isRunning = false;
    }
  }

  isEmpty() {
    return this.array.length ? false : true;
  }

  length() {
    return this.array.length;
  }
}
