!function(e){function n(r,o){if(t[r])return t[r].exports;var a={i:r,l:!1,exports:{}};return 0!=o&&(t[r]=a),e[r].call(a.exports,a,a.exports,n),a.l=!0,a.exports}var t={};n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{configurable:!1,enumerable:!0,get:r})},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},n.p="/static-dist/",n(n.s="215bf975c301245d315e")}({"215bf975c301245d315e":function(e,n,t){"use strict";!function(){$(".js-save-btn").click(function(){o.form()&&($(".js-save-btn").button("loading"),$.post($("#enrollment-verify-form").attr("action"),$("#enrollment-verify-form").serialize(),function(){window.location.reload()}))})}();var r=$("#enrollment-verify-form");$("[name='verifyStatus']").change(function(){"rejected"===$(this).val()?($(".js-approved").addClass("hidden"),$(".js-reason").removeClass("hidden")):($(".js-approved").removeClass("hidden"),$(".js-reason").addClass("hidden"))});var o=r.validate({rules:{rejectedReason:{maxlength:200}}})}});