!function(e){function n(t,i){if(r[t])return r[t].exports;var o={i:t,l:!1,exports:{}};return 0!=i&&(r[t]=o),e[t].call(o.exports,o,o.exports,n),o.l=!0,o.exports}var r={};n.m=e,n.c=r,n.d=function(e,r,t){n.o(e,r)||Object.defineProperty(e,r,{configurable:!1,enumerable:!0,get:t})},n.n=function(e){var r=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(r,"a",r),r},n.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},n.p="/static-dist/",n(n.s="bff60b914124f82c23c5")}({bff60b914124f82c23c5:function(e,n,r){"use strict";function t(e,n){if(!(e instanceof n))throw new TypeError("Cannot call a class as a function")}var i=function(){function e(e,n){for(var r=0;r<n.length;r++){var t=n[r];t.enumerable=t.enumerable||!1,t.configurable=!0,"value"in t&&(t.writable=!0),Object.defineProperty(e,t.key,t)}}return function(n,r,t){return r&&e(n.prototype,r),t&&e(n,t),n}}();new(function(){function e(){t(this,e),this.init()}return i(e,[{key:"init",value:function(){var e=$("#admin-complete-info-form"),n=e.validate({rules:{applicant:{byte_maxlength:30,required:!0},companyName:{required:!0},province:{required:!0},city:{required:!0},industry:{required:!0},employeeNumber:{unsigned_integer:!0,min:1,max:1e6,required:!0}},messages:{province:{required:Translator.trans("请选择省"),trim:Translator.trans("请选择省")},city:{required:Translator.trans("请选择市"),trim:Translator.trans("请选择市")},industry:{required:Translator.trans("请选择行业"),trim:Translator.trans("请选择行业")}}});$("select#province").on("change",function(e,n){$.get($("#city").data("url"),{id:$("#province").val()},function(e){$("#city").empty(),$("select#city").append("<option value=''>-- 市 --</option>"),e.map(function(e,n,r){$("select#city").append("<option value='"+e.id+"'>"+e.name+"</option>")})})}),$("#form-submit").click(function(r){n.form()&&($(r.currentTarget).button("loading"),e.submit())})}}]),e}())}});