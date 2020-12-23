var log = function(msg) {
  if (typeof console !== 'undefined') {
    console && console.error && console.error(msg);
  }
};
if (!echarts) {
  log('ECharts is not Loaded');
} else {
  echarts.registerTheme('corporateTraining', {
    'color': [
      '#f0354b',
      '#ffa500',
      '#19c08c',
      '#0093ff',
      '#3a60df',
      '#8d98b3',
      '#e5cf0d',
      '#97b552',
      '#95706d',
      '#dc69aa',
      '#07a2a4',
      '#9a7fd1',
      '#588dd5',
      '#f5994e',
      '#c05050',
      '#59678c',
      '#c9ab00',
      '#7eb00a',
      '#6f5553',
      '#c14089'
    ],
    'backgroundColor': 'rgba(0,0,0,0)',
    'textStyle': {},
    'title': {
      'textStyle': {
        'color': '#616161'
      },
      'subtextStyle': {
        'color': '#919191'
      }
    },
    'line': {
      'itemStyle': {
        'normal': {
          'borderWidth': '1'
        }
      },
      'lineStyle': {
        'normal': {
          'width': '1'
        }
      },
      'symbolSize': '6',
      'symbol': 'emptyCircle',
      'smooth': true
    },
    'radar': {
      'itemStyle': {
        'normal': {
          'borderWidth': '1'
        }
      },
      'lineStyle': {
        'normal': {
          'width': '1'
        }
      },
      'symbolSize': '6',
      'symbol': 'emptyCircle',
      'smooth': true
    },
    'bar': {
      'itemStyle': {
        'normal': {
          'barBorderWidth': '0',
          'barBorderColor': '#cccccc'
        },
        'emphasis': {
          'barBorderWidth': '0',
          'barBorderColor': '#cccccc'
        }
      }
    },
    'pie': {
      'itemStyle': {
        'normal': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        },
        'emphasis': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        }
      }
    },
    'scatter': {
      'itemStyle': {
        'normal': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        },
        'emphasis': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        }
      }
    },
    'boxplot': {
      'itemStyle': {
        'normal': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        },
        'emphasis': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        }
      }
    },
    'parallel': {
      'itemStyle': {
        'normal': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        },
        'emphasis': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        }
      }
    },
    'sankey': {
      'itemStyle': {
        'normal': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        },
        'emphasis': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        }
      }
    },
    'funnel': {
      'itemStyle': {
        'normal': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        },
        'emphasis': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        }
      }
    },
    'gauge': {
      'itemStyle': {
        'normal': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        },
        'emphasis': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        }
      }
    },
    'candlestick': {
      'itemStyle': {
        'normal': {
          'color': '#d87a80',
          'color0': '#2ec7c9',
          'borderColor': '#d87a80',
          'borderColor0': '#2ec7c9',
          'borderWidth': 1
        }
      }
    },
    'graph': {
      'itemStyle': {
        'normal': {
          'borderWidth': '0',
          'borderColor': '#cccccc'
        }
      },
      'lineStyle': {
        'normal': {
          'width': 1,
          'color': '#aaaaaa'
        }
      },
      'symbolSize': '6',
      'symbol': 'emptyCircle',
      'smooth': true,
      'color': [
        '#f0354b',
        '#ffa500',
        '#19c08c',
        '#0093ff',
        '#3a60df',
        '#8d98b3',
        '#e5cf0d',
        '#97b552',
        '#95706d',
        '#dc69aa',
        '#07a2a4',
        '#9a7fd1',
        '#588dd5',
        '#f5994e',
        '#c05050',
        '#59678c',
        '#c9ab00',
        '#7eb00a',
        '#6f5553',
        '#c14089'
      ],
      'label': {
        'normal': {
          'textStyle': {
            'color': '#ffffff'
          }
        }
      }
    },
    'map': {
      'itemStyle': {
        'normal': {
          'areaColor': '#dddddd',
          'borderColor': '#eeeeee',
          'borderWidth': 0.5
        },
        'emphasis': {
          'areaColor': 'rgba(254,153,78,1)',
          'borderColor': '#444444',
          'borderWidth': 1
        }
      },
      'label': {
        'normal': {
          'textStyle': {
            'color': '#d87a80'
          }
        },
        'emphasis': {
          'textStyle': {
            'color': 'rgb(100,0,0)'
          }
        }
      }
    },
    'geo': {
      'itemStyle': {
        'normal': {
          'areaColor': '#dddddd',
          'borderColor': '#eeeeee',
          'borderWidth': 0.5
        },
        'emphasis': {
          'areaColor': 'rgba(254,153,78,1)',
          'borderColor': '#444444',
          'borderWidth': 1
        }
      },
      'label': {
        'normal': {
          'textStyle': {
            'color': '#d87a80'
          }
        },
        'emphasis': {
          'textStyle': {
            'color': 'rgb(100,0,0)'
          }
        }
      }
    },
    'categoryAxis': {
      'axisLine': {
        'show': true,
        'lineStyle': {
          'color': 'rgba(0,0,0,0.08)'
        }
      },
      'axisTick': {
        'show': false,
        'lineStyle': {
          'color': '#919191'
        }
      },
      'axisLabel': {
        'show': true,
        'textStyle': {
          'color': '#919191'
        }
      },
      'splitLine': {
        'show': false,
        'lineStyle': {
          'color': [
            '#eee'
          ]
        }
      },
      'splitArea': {
        'show': false,
        'areaStyle': {
          'color': [
            'rgba(250,250,250,0.3)',
            'rgba(200,200,200,0.3)'
          ]
        }
      }
    },
    'valueAxis': {
      'axisLine': {
        'show': false,
        'lineStyle': {
          'color': 'rgba(0,0,0,0.08)'
        }
      },
      'axisTick': {
        'show': false,
        'lineStyle': {
          'color': '#333'
        }
      },
      'axisLabel': {
        'show': true,
        'textStyle': {
          'color': '#919191'
        }
      },
      'splitLine': {
        'show': true,
        'lineStyle': {
          'color': [
            '#eee'
          ]
        }
      },
      'splitArea': {
        'show': false,
        'areaStyle': {
          'color': [
            'rgba(250,250,250,0.3)',
            'rgba(200,200,200,0.3)'
          ]
        }
      }
    },
    'logAxis': {
      'axisLine': {
        'show': true,
        'lineStyle': {
          'color': 'rgba(0,0,0,0.08)'
        }
      },
      'axisTick': {
        'show': false,
        'lineStyle': {
          'color': '#333'
        }
      },
      'axisLabel': {
        'show': true,
        'textStyle': {
          'color': '#919191'
        }
      },
      'splitLine': {
        'show': true,
        'lineStyle': {
          'color': [
            '#eee'
          ]
        }
      },
      'splitArea': {
        'show': true,
        'areaStyle': {
          'color': [
            'rgba(250,250,250,0.3)',
            'rgba(200,200,200,0.3)'
          ]
        }
      }
    },
    'timeAxis': {
      'axisLine': {
        'show': true,
        'lineStyle': {
          'color': '#333333'
        }
      },
      'axisTick': {
        'show': false,
        'lineStyle': {
          'color': '#333'
        }
      },
      'axisLabel': {
        'show': true,
        'textStyle': {
          'color': '#919191'
        }
      },
      'splitLine': {
        'show': true,
        'lineStyle': {
          'color': [
            '#eeeeee'
          ]
        }
      },
      'splitArea': {
        'show': false,
        'areaStyle': {
          'color': [
            'rgba(250,250,250,0.3)',
            'rgba(200,200,200,0.3)'
          ]
        }
      }
    },
    'toolbox': {
      'iconStyle': {
        'normal': {
          'borderColor': '#919191'
        },
        'emphasis': {
          'borderColor': '#616161'
        }
      }
    },
    'legend': {
      'textStyle': {
        'color': '#616161'
      }
    },
    'tooltip': {
      'axisPointer': {
        'lineStyle': {
          'color': 'rgba(0,0,0,0.08)',
          'width': '1'
        },
        'crossStyle': {
          'color': 'rgba(0,0,0,0.08)',
          'width': '1'
        }
      }
    },
    'timeline': {
      'lineStyle': {
        'color': '#008acd',
        'width': 1
      },
      'itemStyle': {
        'normal': {
          'color': '#008acd',
          'borderWidth': 1
        },
        'emphasis': {
          'color': '#a9334c'
        }
      },
      'controlStyle': {
        'normal': {
          'color': '#008acd',
          'borderColor': '#008acd',
          'borderWidth': 0.5
        },
        'emphasis': {
          'color': '#008acd',
          'borderColor': '#008acd',
          'borderWidth': 0.5
        }
      },
      'checkpointStyle': {
        'color': '#2ec7c9',
        'borderColor': 'rgba(46,199,201,0.4)'
      },
      'label': {
        'normal': {
          'textStyle': {
            'color': '#008acd'
          }
        },
        'emphasis': {
          'textStyle': {
            'color': '#008acd'
          }
        }
      }
    },
    'visualMap': {
      'color': [
        '#5ab1ef',
        '#e0ffff'
      ]
    },
    'dataZoom': {
      'backgroundColor': 'rgba(47,69,84,0)',
      'dataBackgroundColor': 'rgba(239,239,255,1)',
      'fillerColor': 'rgba(182,162,222,0.2)',
      'handleColor': '#008acd',
      'handleSize': '100%',
      'textStyle': {
        'color': '#333333'
      }
    },
    'markPoint': {
      'label': {
        'normal': {
          'textStyle': {
            'color': '#ffffff'
          }
        },
        'emphasis': {
          'textStyle': {
            'color': '#ffffff'
          }
        }
      }
    }
  });
}

