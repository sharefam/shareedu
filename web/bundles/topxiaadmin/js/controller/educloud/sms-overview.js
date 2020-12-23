define(function(require, exports, module) {
    require('echarts');
    exports.run = function() {

        //改版图表
        var smsSendChart = echarts.init(document.getElementById('smsSendChart'));
        var chartData = app.arguments.chartData;
        var option = {
            title: {
                text: ''
            },
            tooltip: {},
            legend: {
                data:[Translator.trans('admin.educloud.sms_overview.time')]
            },
            xAxis: {
                data: chartData.date
            },
            yAxis: {
                minInterval: 1
            },
            series: [{
                name: Translator.trans('admin.educloud.sms_overview.chart_series_name'),
                type: 'bar',
                data: chartData.count
            }],
            color:['#428BCA'],
            grid:{
                show:true,
                borderColor:'#fff',
                backgroundColor:'#fff'
            }
        };
        smsSendChart.setOption(option);            
    }

});
