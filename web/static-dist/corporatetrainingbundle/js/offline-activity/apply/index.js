!function(t){function n(e,r){if(o[e])return o[e].exports;var i={i:e,l:!1,exports:{}};return 0!=r&&(o[e]=i),t[e].call(i.exports,i,i.exports,n),i.l=!0,i.exports}var o={};n.m=t,n.c=o,n.d=function(t,o,e){n.o(t,o)||Object.defineProperty(t,o,{configurable:!1,enumerable:!0,get:e})},n.n=function(t){var o=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(o,"a",o),o},n.o=function(t,n){return Object.prototype.hasOwnProperty.call(t,n)},n.p="/static-dist/",n(n.s="4fa361281d8b96a8479d")}({"4fa361281d8b96a8479d":function(t,n,o){"use strict";!function(){$(".js-save-btn").click(function(){$(".js-save-btn").hasClass("disabled")||($(".js-save-btn").button("loading"),$.post($("#offline-activity-apply-form").attr("action"),$("#offline-activity-apply-form").serialize(),function(t){window.location.reload()},"json"))})}()}});