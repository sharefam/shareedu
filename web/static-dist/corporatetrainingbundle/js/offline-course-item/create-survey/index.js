!function(e){function t(n,i){if(r[n])return r[n].exports;var o={i:n,l:!1,exports:{}};return 0!=i&&(r[n]=o),e[n].call(o.exports,o,o.exports,t),o.l=!0,o.exports}var r={};t.m=e,t.c=r,t.d=function(e,r,n){t.o(e,r)||Object.defineProperty(e,r,{configurable:!1,enumerable:!0,get:n})},t.n=function(e){var r=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(r,"a",r),r},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="/static-dist/",t(t.s="8bb2be5599084dbd1cb5")}({"8bb2be5599084dbd1cb5":function(e,t,r){"use strict";function n(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var i=function(){function e(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}return function(t,r,n){return r&&e(t.prototype,r),n&&e(t,n),t}}(),o=function(){function e(){n(this,e),this.$form=$("#offline-course-task-create-form"),this.$modal=this.$form.parents(".modal"),this.init()}return i(e,[{key:"init",value:function(){this.initValidator()}},{key:"initValidator",value:function(){var e=this;this.validator=this.$form.validate({rules:{title:{maxlength:60,required:{depends:function(){return $(this).val($.trim($(this).val())),!0}},course_title:!0},mediaId:{required:!0}},messages:{mediaId:{required:Translator.trans("offline_course.choose_survey"),trim:Translator.trans("offline_course.choose_survey")}}}),$("#create-form-submit").on("click",function(t){e.validator.form()&&($("#create-form-submit").button("loading"),e.$form.submit())})}}]),e}();t.default=o,new o}});