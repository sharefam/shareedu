!function(t){function e(r,o){if(n[r])return n[r].exports;var i={i:r,l:!1,exports:{}};return 0!=o&&(n[r]=i),t[r].call(i.exports,i,i.exports,e),i.l=!0,i.exports}var n={};e.m=t,e.c=n,e.d=function(t,n,r){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:r})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="/static-dist/",e(e.s="b3ed291d96296fc3e991")}({b3ed291d96296fc3e991:function(t,e,n){"use strict";var r=$("#modal").get(0).__relation__;$(".js-unit-addition-select").on("click",".js-unit-addition-select-item",function(){$(this).addClass("active");var t=$(this).data("url");r.remote(t)}),$("[data-toggle='popover']").popover()}});