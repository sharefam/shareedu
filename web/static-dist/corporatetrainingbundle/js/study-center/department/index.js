!function(t){function e(r,a){if(n[r])return n[r].exports;var u={i:r,l:!1,exports:{}};return 0!=a&&(n[r]=u),t[r].call(u.exports,u,u.exports,e),u.l=!0,u.exports}var n={};e.m=t,e.c=n,e.d=function(t,n,r){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:r})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="/static-dist/",e(e.s="168c367a3da00e8831d8")}({"168c367a3da00e8831d8":function(t,e,n){"use strict";var r=$("#postId");r.select2({ajax:{url:r.data("url"),dataType:"json",quietMillis:100,data:function(t,e){return{q:t,page_limit:10}},results:function(t){var e=[];return $.each(t,function(t,n){e.push({id:n.id,name:n.name})}),{results:e}}},formatSelection:function(t){return t.name},formatResult:function(t){return t.name},initSelection:function(t,e){var n=$(t).val();if(""!==n){e({id:n,name:$(t).data("name")})}},placeholder:Translator.trans("study_center.department.all_post")})}});