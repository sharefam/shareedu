webpackJsonp(["app/js/user/message/index"],{0:function(e,t){e.exports=jQuery},"09eb1f9807af90690645":function(e,t,n){"use strict";function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var a=function(){function e(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(t,n,r){return n&&e(t.prototype,n),r&&e(t,r),t}}(),s=n("b334fd7e4c5a19234db2"),u=function(e){return e&&e.__esModule?e:{default:e}}(s),o=function(){function e(t){r(this,e),this.$element=$(t.element),this.validator()}return a(e,[{key:"validator",value:function(){var e=this.$element;e.validate({rules:{"message[receiver]":{required:!0,es_remote:!0},"message[content]":{required:!0,maxlength:500}},ajax:!0,submitSuccess:function(){(0,u.default)("success",Translator.trans("private_message.sent_success")),e.closest(".modal").modal("hide")},submitError:function(e){(0,u.default)("danger",Translator.trans(e.responseJSON.error.message))}})}}]),e}();t.default=o},d01bff5149c3afb398fc:function(e,t,n){"use strict";var r=n("09eb1f9807af90690645");new(function(e){return e&&e.__esModule?e:{default:e}}(r).default)({element:"#message-create-form"})}},["d01bff5149c3afb398fc"]);