define(function(require, exports, module) {

    require('../widget/category-select').run('course');

    exports.run = function(options) {
    	new window.$.CheckTreeviewInput({
		  $elem: $('#orgCode'),
		  selectType: 'single',
		});
    };

});