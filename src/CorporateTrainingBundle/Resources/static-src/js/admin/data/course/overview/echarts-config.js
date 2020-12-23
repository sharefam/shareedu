const inner = {
  type: 'pie',
  radius: ['0', '15%'],
  label: {
    normal: {
      textStyle: {
        color: '#000'
      },
      formatter: function(value) {
        return formatter(value, 3);
      }
    }
  },
  labelLine: {
    normal: {
      length: 2,
      length2: 5
    }
  }
};

const middle = {
  type: 'pie',
  radius: ['30%', '45%'],
  label: {
    normal: {
      textStyle: {
        color: '#000'
      },
      formatter: function(value) {
        return formatter(value, 4);
      }
    }
  },
  labelLine: {
    normal: {
      length: 2,
      length2: 5
    }
  }
};

const outer = {
  type: 'pie',
  radius: ['60%', '75%']
};

const levelConfig = [inner, middle, outer];
const formatter = (value, len) => {
  return value && value.name.length > len ? value.name.substring(0, len) + '..' : value.name;
};

const pieOptions = {
  tooltip: {
    trigger: 'item',
    formatter: '{a} <br/>{b}: {c} ({d}%)'
  },
  legend: {
    orient: 'vertical',
    x: 'left',
    data: []
  },
  series: []
};

export {
  pieOptions,
  levelConfig
};